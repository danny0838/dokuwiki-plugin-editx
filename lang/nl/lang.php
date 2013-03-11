<?php
/**
 * Dutch language file by Theo Klein
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
 
$lang['title']              = '%s Beheer';
$lang['redirecttext']       = 'Deze pagina is verplaatst naar [[:@ID@]].';
$lang['pagemanagement']     = 'paginabeheer';
 
// rename page = rp
$lang['rp_title']           = 'Pagina hernoemen';
$lang['rp_newpage']         = 'Nieuwe naam';
$lang['rp_summary']         = 'Reden';
$lang['rp_nr']              = 'Laat geen verwijzing achter op de oude pagina.';
$lang['rp_confirm']         = 'Verplaatsing bevestigen';
 
$lang['rp_oldsummary']      = '(Verwijderen) %s verplaatst naar %s';
$lang['rp_oldsummaryx']     = '(Verwijderen) %s verplaatst naar %s (%s)';
$lang['rp_newsummary']      = '%s verplaatst naar %s';
$lang['rp_newsummaryx']     = '%s verplaatst naar %s (%s)';
 
$lang['rp_msg_unconfirmed'] = 'De optie "Verplaatsing bevestigen" moet aangevinkt zijn om een pagina te verplaatsen.';
$lang['rp_msg_old_empty']   = 'De oude paginanaam kan niet leeg zijn.';
$lang['rp_msg_old_noexist'] = 'De oude pagina %s bestaat niet.';
$lang['rp_msg_new_empty']   = 'De nieuwe paginanaam kan niet leeg zijn.';
$lang['rp_msg_new_exist']   = 'De nieuwe pagina %s bestaat reeds.';
$lang['rp_msg_new_hx_exist']= 'De nieuwe pagina %s heeft reeds een historie.';
$lang['rp_msg_locked']      = 'Pagina %s is nu vergrendeld.';
$lang['rp_msg_auth']        = 'U bent niet bevoegd om de volgende pagina te wijzigen: %s.';
$lang['rp_msg_auth_nr']     = 'U bent niet bevoegd om de verwijzing weg te laten.';
#$lang['rp_msg_file_conflict'] = 'Er is een conflict ontstaan met het bestand %s van de nieuwe pagina. <a href="%s">Bekijk de eerdere revisies van deze pagina</a> of gebruik <a href="%s">Paginabeheer</a> om de historie van deze pagina te verwijderen.';
$lang['rp_msg_success']     = 'Pagina %s succesvol verplaatst naar %s.';
 
// delete page = dp
$lang['dp_title']           = 'Pagina verwijderen';
$lang['dp_purge']           = 'Pagina verwijderen inclusief historie';
$lang['dp_confirm']         = 'Verwijderen bevestigen';
$lang['dp_summary']         = 'Reden';
 
$lang['dp_oldsummary']      = 'Verwijderd';
$lang['dp_oldsummaryx']     = '(%s) verwijderd';
 
$lang['dp_msg_unconfirmed'] = 'De optie "Verwijderen bevestigen" moet aangevinkt zijn om een pagina te verwijderen.';
$lang['dp_msg_old_empty']   = 'De oude paginanaam kan niet leeg zijn.';
$lang['dp_msg_auth']        = 'U bent niet bevoegd om de volgende pagina te verwijderen: %s.';
$lang['dp_msg_auth_new']    = 'U bent niet bevoegd om de volgende pagina te wijzigen: %s.';
$lang['dp_msg_success']     = 'Pagina %s succesvol verwijderd.';
 
//Setup VIM: ex: et ts=2 enc=utf-8 :
