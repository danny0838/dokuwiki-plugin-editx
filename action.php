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
                $oldpage = cleanID($_REQUEST['oldpage']);
                $newpage = cleanID($_REQUEST['newpage']);
                $summary = $_REQUEST['summary'];
                $this->_rename_page($oldpage, $newpage, $summary);
                break;
            case 'delete':
                $oldpage = cleanID($_REQUEST['oldpage']);
                $mode = (int)$_REQUEST['mode'];
                $summary = $_REQUEST['summary'];
                $this->_delete_page($oldpage, $mode, $summary);
                break;
            case 'recover':
                $summary = $_REQUEST['summary'];
                $this->_recover_page($summary);
                break;
            default:
                $this->_print_form();
                break;
        }
    }
    
    function _get_delete_ns() {
        static $deletens = null;
        if ($deletens==null) {
            $ns = cleanID($this->getConf('deletens'));
            if (!$ns) $ns = 'delete';
            $deletens = $ns;
        }
        return $deletens;
    }
    
    function _rename_page($oldpage, $newpage, $summary) {
        // check old page
        if (!$oldpage) {
            $this->errors[] = $this->getLang('rp_msg_old_empty');
        } else if (!page_exists($oldpage)) {
            $this->errors[] = sprintf( $this->getLang('rp_msg_old_noexist'), $oldpage );
        } else if (auth_quickaclcheck($oldpage)<AUTH_EDIT) {
            $this->errors[] = sprintf( $this->getLang('rp_msg_auth'), $oldpage );
        } else if (checklock($oldpage)) {
            $this->errors[] = sprintf( $this->getLang('rp_msg_locked'), $oldpage );
        }
        // check new page
        if (!$newpage) {
            $this->errors[] = $this->getLang('rp_msg_new_empty');
        } else if (page_exists($newpage)) {
            $this->errors[] = sprintf( $this->getLang('rp_msg_new_exist'), $newpage );
        } else if (auth_quickaclcheck($newpage)<AUTH_EDIT) {
            $this->errors[] = sprintf( $this->getLang('rp_msg_auth'), $newpage );
        } else if (checklock($newpage)) {
            $this->errors[] = sprintf( $this->getLang('rp_msg_locked'), $newpage );
        }
        // if no error do rename
        if (count($this->errors)==0) {
            $this->_rename_page_work($oldpage, $newpage, $summary);
        }
        // show error messages
        if (count($this->errors)>0) {
            foreach ($this->errors as $error) msg( $error, -1 );
        }
        // display form and table
        $data = array( rp_newpage => $newpage, rp_summary => $summary );
        $this->_print_form($data);
    }
    
    function _rename_page_work($oldpage, $newpage, $summary) {
        // make new ns folder
        $newmetadir = dirname(metaFN($newpage,'.txt'));
        $newatticdir = dirname(wikiFN($newpage,'1'));
        if (!io_mkdir_p($newmetadir)) return false;
        if (!io_mkdir_p($newatticdir)) return false;
        $oldpagebase = noNS($oldpage);
        $newpagebase = noNS($newpage);
        // move meta and attic
        $oldmetas = metaFiles($oldpage);
        foreach ($oldmetas as $fold ) {
            $ext = end(explode( $oldpagebase, $fold ));
            if ($ext == '.meta' || $ext == '.indexed') continue;
            $fnew = $newmetadir.'/'.$newpagebase.$ext;
            if (@file_exists($fnew)) {
                $this->errors[] = sprintf( $this->getLang('rp_msg_new_hx_exist'), $newpage );
                return false;
            }
            $tasks[] = array($fold,$fnew);
        }
        $oldattics = glob(wikiFN($oldpage,'*'));
        foreach ($oldattics as $fold ) {
            $ext = end(explode( $oldpagebase, $fold ));
            $fnew = $newatticdir.'/'.$newpagebase.$ext;
            if (@file_exists($fnew)) {
                $this->errors[] = sprintf( $this->getLang('rp_msg_new_hx_exist'), $newpage );
                return false;
            }
            $tasks[] = array($fold,$fnew);
        }
        foreach ($tasks as $task) rename($task[0],$task[1]);
        // save to newpage
        $text = rawWiki($oldpage);
        if ($summary)
            $sum = sprintf( $this->getLang('rp_newsummaryx'), $oldpage, $newpage, $summary );
        else
            $sum = sprintf( $this->getLang('rp_newsummary'), $oldpage, $newpage );
        saveWikiText($newpage,$text,$sum);
        // recreate old page
        $text = "~~REDIRECT>:$newpage~~";
        if ($summary)
            $sum = sprintf( $this->getLang('rp_oldsummaryx'), $oldpage, $newpage, $summary );
        else
            $sum = sprintf( $this->getLang('rp_oldsummary'), $oldpage, $newpage );
        @unlink(wikiFN($oldpage));  // remove old page file so no additional history
        saveWikiText($oldpage,$text,$sum);
        // show messages
        if (!$this->errors) {
            $msg = sprintf( $this->getLang('rp_msg_success'), $oldpage, $newpage );
            msg( $msg, 1 );
        }
        return true;
    }
    
    function _delete_page($oldpage, $mode, $summary) {
        // check old page
        if (!$oldpage) {
            $this->errors[] = $this->getLang('dp_msg_old_empty');
        } else if (auth_quickaclcheck($oldpage)<AUTH_DELETE) {
            $this->errors[] = sprintf( $this->getLang('dp_msg_auth'), $oldpage );
        }
        // if no error do delete
        if (count($this->errors)==0) {
            switch ($mode) {
                case 1:
                    $this->_delete_page_purge($oldpage, $summary);
                    break;
                case 0:
                default:
                    $this->_delete_page_delete($oldpage, $summary);
                    break;
            }
        }
        // show error messages
        if (count($this->errors)>0) {
            foreach ($this->errors as $error) msg( $error, -1 );
        }
        // display form and table
        $data = array( dp_mode => $mode, dp_summary => $summary );
        $this->_print_form($data);
    }
    
    function _delete_page_delete($oldpage, $summary) {
        if ($summary) 
            $sum = sprintf( $this->getLang('dp_oldsummaryx'), $summary );
        else 
            $sum = sprintf( $this->getLang('dp_oldsummary') );
        $err = $this->_delete_page_delete_work($oldpage, $sum);
        // show messages
        if (!$err) {
            $msg = sprintf( $this->getLang('dp_msg_d_success'), $oldpage );
            msg( $msg, 1 );
        }
    }
    
    function _delete_page_purge($oldpage, $summary) {
        $err = $this->_delete_page_delete_work($oldpage, $sum, true);
        // show messages
        if (!$err) {
            $msg = sprintf( $this->getLang('dp_msg_p_success'), $oldpage );
            msg( $msg, 1 );
        }
    }

    function _delete_page_delete_work($oldpage, $sum, $purge=false) {
        $oldpagebase = noNS($oldpage);
        // pure correlation with other pages
        if (page_exists($oldpage))
            saveWikiText($oldpage,'',$sum);
        else
            addLogEntry( null, $oldpage, DOKU_CHANGE_TYPE_DELETE, $sum );
        // delete meta and attic
        $oldmetas = metaFiles($oldpage);
        foreach ($oldmetas as $file ) {
            $ext = end(explode( $oldpagebase, $file ));
            if ($ext=='.changes' && !$purge) {
                $change = end(file($file,FILE_SKIP_EMPTY_LINES));
                file_put_contents( $file, $change ) or $err++;
            }
            else if ($ext=='.mlist' && !$purge) {} // keep subscriber list
            else unlink($file) or $err++;
        }
        $oldattics = glob(wikiFN($oldpage,'*'));
        foreach ($oldattics as $file ) {
            unlink($file) or $err++;
        }
        if ($err) {
            $this->errors[] = sprintf( $this->getLang('dp_msg_unclear'), $oldpage);
        }
        return $err;
    }

    function _print_form($data=null) {
        global $ID, $lang;
        $sel = ' selected="selected"';
        $deletens = $this->_get_delete_ns();
        $isdeletens = preg_match( "#^$deletens:\d+:#", $ID );
?>
<h1><?php echo sprintf( $this->getLang('title'), $ID); ?></h1>
<div id="config__manager">
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
    if (auth_quickaclcheck($ID)>=AUTH_DELETE) {
?>
    <form action="<?php echo wl($ID); ?>" method="post">
    <fieldset>
    <legend><?php echo $this->getLang('dp_title'); ?></legend>
        <input type="hidden" name="do" value="editx" />
        <input type="hidden" name="work" value="delete" />
        <input type="hidden" name="oldpage" value="<?php echo $ID; ?>" />
        <table class="inline">
            <tr>
                <td class="label"><?php echo $this->getLang('dp_mode'); ?></td>
                <td class="value">
                    <select name="mode">
                        <option value="0"<?php if (!$data['dp_mode']) echo $sel;?>><?php echo $this->getLang('dp_mode_delete'); ?></option>
                        <option value="1"<?php if ($data['dp_mode']==1) echo $sel;?>><?php echo $this->getLang('dp_mode_purge'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="label"><?php echo $this->getLang('dp_summary'); ?></td>
                <td class="value"><input class="edit" type="input" name="summary" value="<?php echo $data['dp_summary']; ?>" /></td>
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
