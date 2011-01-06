<?php
/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Danny Lin <danny0838@pchome.com.tw>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class action_plugin_editx extends DokuWiki_Action_Plugin {

    /**
     * register the eventhandlers
     */
    function register(&$contr) {
        $contr->register_hook('TPL_ACT_RENDER', 'BEFORE', $this, '_prepend_to_edit', array());
        $contr->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, '_handle_act', array());
        $contr->register_hook('TPL_ACT_UNKNOWN', 'BEFORE', $this, '_handle_tpl_act', array());
    }

    /**
     * main hooks
     */ 
    function _prepend_to_edit(&$event, $param) {
        if ($event->data != 'edit') return;
        if (auth_quickaclcheck($ID)<AUTH_EDIT) return;
        global $ID;
        $link = html_wikilink($ID.'?do=editx');
        $intro = $this->locale_xhtml('intro');
        $intro = str_replace( '@LINK@', $link, $intro );
        print $intro;
    }

    function _handle_act(&$event, $param) {
        if($event->data != 'editx') return;
        $event->preventDefault();
    }

    function _handle_tpl_act(&$event, $param) {
        if($event->data != 'editx') return;
        $event->preventDefault();

        switch ($_REQUEST['work']) {
            case 'rename':
                $opts['oldpage'] = cleanID($_REQUEST['oldpage']);
                $opts['newpage'] = cleanID($_REQUEST['newpage']);
                $opts['summary'] = $_REQUEST['summary'];
                $this->_rename_page($opts);
                break;
            case 'delete':
                $opts['oldpage'] = cleanID($_REQUEST['oldpage']);
                $opts['summary'] = $_REQUEST['summary'];
                $opts['purge'] = $_REQUEST['dp_purge'];
                $this->_delete_page($opts);
                break;
            default:
                $this->_print_form();
                break;
        }
    }

    /**
     * helper functions
     */
    function _auth_can_rename($id) {
        return auth_quickaclcheck($id)>=AUTH_EDIT;
    }

    function _auth_can_rename_nr($id) {
        return auth_quickaclcheck($id)>=AUTH_DELETE;
    }

    function _auth_can_delete($id) {
        return auth_quickaclcheck($id)>=AUTH_DELETE;
    }
    
    function _locate_filepairs(&$opts, $dir, $regex ){
        global $conf;
        $oldpath = $conf[$dir].'/'.str_replace(':','/',$opts['oldns']);
        $newpath = $conf[$dir].'/'.str_replace(':','/',$opts['newns']);
        $dh = @opendir($oldpath);
        if($dh) {
            while(($file = readdir($dh)) !== false){
                if ($file{0}=='.') continue;
                $oldfile = $oldpath.$file;
                if (is_file($oldfile) && preg_match($regex,$file)){
                    $opts['oldfiles'][] = $oldfile;
                    if ($opts['move']) {
                        $newfilebase = str_replace($opts['oldname'], $opts['newname'], $file);
                        $newfile = $newpath.$newfilebase;
                        if (@file_exists($newfile)) {
                            $this->errors[] = sprintf( $this->getLang('rp_msg_file_conflict'), $newfilebase );
                            return false;
                        }
                        $opts['newfiles'][] = $newfile;
                    }
                }
            }
            closedir($dh);
            return true;
        }
        return false;
    }

    /**
     * main functions
     */
    function _rename_page(&$opts) {
        // check old page
        if (!$opts['oldpage']) {
            $this->errors[] = $this->getLang('rp_msg_old_empty');
        } else if (!page_exists($opts['oldpage'])) {
            $this->errors[] = sprintf( $this->getLang('rp_msg_old_noexist'), $opts['oldpage'] );
        } else if (!$this->_auth_can_rename($opts['oldpage'])) {
            $this->errors[] = sprintf( $this->getLang('rp_msg_auth'), $opts['oldpage'] );
        } else if (checklock($opts['oldpage'])) {
            $this->errors[] = sprintf( $this->getLang('rp_msg_locked'), $opts['oldpage'] );
        }
        // check new page
        if (!$opts['newpage']) {
            $this->errors[] = $this->getLang('rp_msg_new_empty');
        } else if (page_exists($opts['newpage'])) {
            $this->errors[] = sprintf( $this->getLang('rp_msg_new_exist'), $opts['newpage'] );
        } else if (!$this->_auth_can_rename($opts['newpage'])) {
            $this->errors[] = sprintf( $this->getLang('rp_msg_auth'), $opts['newpage'] );
        } else if (checklock($opts['newpage'])) {
            $this->errors[] = sprintf( $this->getLang('rp_msg_locked'), $opts['newpage'] );
        }
        // try to locate moves
        if (!$this->errors) {
            $opts['move'] = true;
            $opts['oldname'] = noNS($opts['oldpage']);
            $opts['newname'] = noNS($opts['newpage']);
            $opts['oldns'] = getNS($opts['oldpage']);
            $opts['newns'] = getNS($opts['newpage']);
            if ($opts['oldns']) $opts['oldns'] .= '/';
            if ($opts['newns']) $opts['newns'] .= '/';
            $this->_locate_filepairs( $opts, 'metadir', '/^'.$opts['oldname'].'\.(?!meta|indexed)\w*?$/' );
            $this->_locate_filepairs( $opts, 'olddir', '/^'.$opts['oldname'].'\.\d{10}\.txt(\.gz|\.bz2)?$/' );
        }
        // if no error do rename
        if (!$this->errors) {
            // move meta and attic
            $this->_apply_moves($opts);
            // save to newpage
            $text = rawWiki($opts['oldpage']);
            if ($opts['summary'])
                $sum = sprintf( $this->getLang('rp_newsummaryx'), $opts['oldpage'], $opts['newpage'], $opts['summary'] );
            else
                $sum = sprintf( $this->getLang('rp_newsummary'), $opts['oldpage'], $opts['newpage'] );
            saveWikiText($opts['newpage'],$text,$sum);
            // recreate old page
            $text = $this->getConf('redirecttext');
            if (!$text) $text = $this->getLang('redirecttext');
            $text = str_replace( '@ID@', $opts['newpage'], $text );
            if ($opts['summary'])
                $sum = sprintf( $this->getLang('rp_oldsummaryx'), $opts['oldpage'], $opts['newpage'], $opts['summary'] );
            else
                $sum = sprintf( $this->getLang('rp_oldsummary'), $opts['oldpage'], $opts['newpage'] );
            @unlink(wikiFN($opts['oldpage']));  // remove old page file so no additional history
            saveWikiText($opts['oldpage'],$text,$sum);
        }
        // show messages
        if ($this->errors) {
            foreach ($this->errors as $error) msg( $error, -1 );
        }
        else {
            $msg = sprintf( $this->getLang('rp_msg_success'), $opts['oldpage'], $opts['newpage'] );
            msg( $msg, 1 );
        }
        // display form and table
        $data = array( rp_newpage => $opts['newpage'], rp_summary => $opts['summary'] );
        $this->_print_form($data);
    }
    
    function _apply_moves(&$opts) {
        foreach ($opts['oldfiles'] as $i => $oldfile) {
            $newfile = $opts['newfiles'][$i];
            $newdir = dirname($newfile);
            if (!@io_mkdir_p($newdir)) {
                $this->errors[] = sprintf( $this->getLang('rp_msg_file_fail'), $newdir);
                continue;
            }
            @io_rename($oldfile, $newfile) or $this->errors[] = sprintf( $this->getLang('rp_msg_file_fail'), $oldfile);
        }
    }

    function _delete_page(&$opts) {
        // check old page
        if (!$opts['oldpage']) {
            $this->errors[] = $this->getLang('dp_msg_old_empty');
        } else if (!$this->_auth_can_delete($opts['oldpage'])) {
            $this->errors[] = sprintf( $this->getLang('dp_msg_auth'), $opts['oldpage'] );
        }
        // if no error do delete
        if (!$this->errors) {
            // save to old page, to pure correlation with other pages
            if ($opts['summary']) 
                $sum = sprintf( $this->getLang('dp_oldsummaryx'), $opts['summary'] );
            else 
                $sum = sprintf( $this->getLang('dp_oldsummary') );
            if (page_exists($opts['oldpage']))
                saveWikiText($opts['oldpage'],'',$sum);
            else
                addLogEntry( null, $opts['oldpage'], DOKU_CHANGE_TYPE_DELETE, $sum );
            // try to locate deletes
            if (!$this->errors) {
                $opts['oldname'] = noNS($opts['oldpage']);
                $opts['oldns'] = getNS($opts['oldpage']);
                if ($opts['oldns']) $opts['oldns'] .= '/';
				if ($opts['purge'])
					$this->_locate_filepairs( $opts, 'metadir', '/^'.$opts['oldname'].'\.\w*?$/', $exclude );
				else
					$this->_locate_filepairs( $opts, 'metadir', '/^'.$opts['oldname'].'\.(?!changes)\w*?$/', $exclude );
                $this->_locate_filepairs( $opts, 'olddir', '/^'.$opts['oldname'].'\.\d{10}\.txt(\.gz|\.bz2)?$/' );
            }
            // delete meta and attic
            $this->_apply_deletes($opts);
            // trim change log
            if (!$opts['purge']) {
                $file = metaFN($opts['oldpage'],'.changes');
                $change = end(file($file,FILE_SKIP_EMPTY_LINES));
                @file_put_contents( $file, $change ) or $this->errors[] = sprintf( $this->getLang('dp_msg_file_fail'), $file);
            }
        }
        // show messages
        if ($this->errors) {
            foreach ($this->errors as $error) msg( $error, -1 );
        }
        else {
            $act = $opts['purge'] ? 'dp_msg_p_success' : 'dp_msg_d_success';
            $msg = sprintf( $this->getLang($act), $opts['oldpage'] );
            msg( $msg, 1 );
        }
        // display form and table
        $data = array( dp_purge => $opts['purge'], dp_summary => $opts['summary'] );
        $this->_print_form($data);
    }
    
    function _apply_deletes(&$opts) {
        foreach ($opts['oldfiles'] as $oldfile) {
            @unlink($oldfile) or $this->errors[] = sprintf( $this->getLang('dp_msg_file_fail'), $oldfile );
        }
    }

    function _print_form($data=null) {
        global $ID, $lang;
        $chk = ' checked="checked"';
?>
<h1><?php echo sprintf( $this->getLang('title'), $ID); ?></h1>
<div id="config__manager">
<?php 
    if ($this->_auth_can_rename($ID)) {
?>
    <form action="<?php echo wl($ID); ?>" method="post">
    <fieldset>
    <legend><?php echo $this->getLang('rp_title'); ?></legend>
        <input type="hidden" name="do" value="editx" />
        <input type="hidden" name="work" value="rename" />
        <input type="hidden" name="oldpage" value="<?php echo $ID; ?>" />
        <table class="inline">
            <tr>
                <td class="label"><?php echo $this->getLang('rp_newpage'); ?></td>
                <td class="value"><input class="edit" type="input" name="newpage" value="<?php echo $data['rp_newpage']; ?>" /></td>
            </tr>
            <tr>
                <td class="label"><?php echo $this->getLang('rp_summary'); ?></td>
                <td class="value"><input class="edit" type="input" name="summary" value="<?php echo $data['rp_summary']; ?>" /></td>
            </tr>
        </table>
        <p>
            <input type="submit" class="button" value="<?php echo $lang['btn_save']; ?>" />
            <input type="reset" class="button" value="<?php echo $lang['btn_reset']; ?>" />
        </p>
    </fieldset>
    </form>
<?php
    }
    if ($this->_auth_can_delete($ID)) {
?>
    <form action="<?php echo wl($ID); ?>" method="post">
    <fieldset>
    <legend><?php echo $this->getLang('dp_title'); ?></legend>
        <input type="hidden" name="do" value="editx" />
        <input type="hidden" name="work" value="delete" />
        <input type="hidden" name="oldpage" value="<?php echo $ID; ?>" />
        <table class="inline">
            <tr>
                <td class="label"><?php echo $this->getLang('dp_summary'); ?></td>
                <td class="value"><input class="edit" type="input" name="summary" value="<?php echo $data['dp_summary']; ?>" /></td>
            </tr>
            <tr>
                <td class="label"><?php echo $this->getLang('dp_purge'); ?></td>
                <td class="value"><input type="checkbox" name="dp_purge" value="1"<?php if ($data['dp_purge']) echo $chk; ?> /></td>
            </tr>
        </table>
        <p>
            <input type="submit" class="button" value="<?php echo $lang['btn_save']; ?>" />
            <input type="reset" class="button" value="<?php echo $lang['btn_reset']; ?>" />
        </p>
    </fieldset>
    </form>
<?php
    }
?>
</div>
<?php
    }
}
// vim:ts=4:sw=4:et:enc=utf-8:
