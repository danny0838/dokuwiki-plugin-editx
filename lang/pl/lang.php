<?php
/**
 * Polish language file
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

$lang['title']              = 'Zarządzanie: %s';
$lang['redirecttext']       = 'Ta strona została przeniesiona do [[:@ID@]].';

// rename page = rp
$lang['rp_title']           = 'Zmiana nazwy strony';
$lang['rp_newpage']         = 'Nowa nazwa';
$lang['rp_summary']         = 'Powód zmiany';
$lang['rp_nr']              = 'Nie przekierowywuj z obecnej nazwy';

$lang['rp_oldsummary']      = '(Usunięto) %s zmieniono na %s'; 
$lang['rp_oldsummaryx']     = '(Usunięto) %s zmieniono na %s (%s)'; 
$lang['rp_newsummary']      = '%s zmieniono na %s';
$lang['rp_newsummaryx']     = '%s zmieniono na %s (%s)';

$lang['rp_msg_old_empty']   = 'Obecna nazwa strony nie może być pusta.';
$lang['rp_msg_old_noexist'] = 'Obecnie nie istnieje strona pod nazwą %s.';
$lang['rp_msg_new_empty']   = 'Nowa nazwa strony nie może być pusta.';
$lang['rp_msg_new_exist']   = 'Strona o proponowanej nazwie %s już istnieje.';
$lang['rp_msg_locked']      = 'Strona %s jest w tej chwili zablokowana.';
$lang['rp_msg_auth']        = 'Nie masz uprawnień do zmiany %s.';
$lang['rp_msg_auth_nr']     = 'Nie masz uprawnień, aby usunąć przekierowanie.';
#$lang['rp_msg_file_conflict'] = 'Wystąpił błąd związany z nową stroną %s.';
$lang['rp_msg_success']     = 'Pomyślnie zmnieniono nazwę strony %s do %s.';

// delete page = dp
$lang['dp_title']           = 'Usuwanie strony';
$lang['dp_purge']           = 'Usuń również historię zmian';
$lang['dp_summary']         = 'Powód usunięcia';

$lang['dp_oldsummary']      = 'Usunięto';
$lang['dp_oldsummaryx']     = 'Usunięto (%s)';

$lang['dp_msg_old_empty']   = 'Obecna nazwa strony nie może być pusta.';
$lang['dp_msg_auth']        = 'Nie masz uprawnień do usunięcia strony %s.';
$lang['dp_msg_auth_new']    = 'Nie masz uprawnień do zmiany %s.'; //'write'? jak to ładnie przetłumaczyć? chyba 'zmiana' będzie ok
$lang['dp_msg_success']     = 'Strona %s została usunięta.';