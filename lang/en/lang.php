<?php
/**
 * English language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

$lang['title']              = '%s Management';
$lang['redirecttext']       = 'This page is redirected to [[:@ID@]].';
$lang['pagemanagement']     = 'page management';

// rename page = rp
$lang['rp_title']           = 'Page Rename';
$lang['rp_newpage']         = 'New name';
$lang['rp_summary']         = 'Reason';
$lang['rp_nr']              = 'Don\'t leave redirection';
$lang['rp_confirm']         = 'Confirm page rename';

$lang['rp_oldsummary']      = '(Delete) %s renamed to %s';
$lang['rp_oldsummaryx']     = '(Delete) %s renamed to %s (%s)';
$lang['rp_newsummary']      = '%s renamed to %s';
$lang['rp_newsummaryx']     = '%s renamed to %s (%s)';

$lang['rp_msg_unconfirmed'] = 'The confirm box must be checked to rename a page.';
$lang['rp_msg_old_empty']   = 'The old pagename cannot be empty.';
$lang['rp_msg_old_noexist'] = 'The old page %s does not exist.';
$lang['rp_msg_new_empty']   = 'The new pagename cannot be empty.';
$lang['rp_msg_new_exist']   = 'The new page %s already exists.';
$lang['rp_msg_locked']      = 'The page %s is locked now.';
$lang['rp_msg_auth']        = 'You are not authorized to edit page %s.';
$lang['rp_msg_auth_nr']     = 'You are not authorized to suppress redirect.';
$lang['rp_msg_file_conflict'] = 'The new page %s has a conflicting file %s.';
$lang['rp_msg_success']     = 'Page %s successfully renamed to %s.';

// delete page = dp
$lang['dp_title']           = 'Page Deletion';
$lang['dp_purge']           = 'Don\'t create delete history';
$lang['dp_confirm']         = 'Confirm page delete';
$lang['dp_summary']         = 'Reason';

$lang['dp_oldsummary']      = 'Deleted';
$lang['dp_oldsummaryx']     = 'Deleted (%s)';

$lang['dp_msg_unconfirmed'] = 'The confirm box must be checked to delete a page.';
$lang['dp_msg_old_empty']   = 'The old pagename cannot be empty.';
$lang['dp_msg_auth']        = 'You are not authorized to delete page %s.';
$lang['dp_msg_auth_new']    = 'You are not authorized to write %s.';
$lang['dp_msg_success']     = 'Page %s successfully deleted.';

//Setup VIM: ex: et ts=2 enc=utf-8 :
