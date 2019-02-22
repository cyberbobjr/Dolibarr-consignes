<?php
// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"]))
  $res = @include($_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php");
// Try main.inc.php into web root detected using web root caluclated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
  $i--;
  $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php"))
  $res = @include(substr($tmp, 0, ($i + 1)) . "/main.inc.php");
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php"))
  $res = @include(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php");
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php"))
  $res = @include("../main.inc.php");
if (!$res && file_exists("../../main.inc.php"))
  $res = @include("../../main.inc.php");
if (!$res && file_exists("../../../main.inc.php"))
  $res = @include("../../../main.inc.php");
if (!$res)
  die("Include of main fails");

llxHeader();
require_once DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';

$langs->loadLangs(["consignes@consignes",
                   "other"]);

if ($user->socid > 0)    // Protection if external user
{
  //$socid = $user->societe_id;
  accessforbidden();
}

$id = GETPOST('socid', 'int');
$societe = new Societe($db);
$result = $societe->fetch($id);

$product = new Product($db);
$extrafields = new ExtraFields($db);
$extralabels = $extrafields->fetch_name_optionals_label($product->table_element);

$hookmanager->initHooks(['thirdpartynote',
                         'globalcard']);

if ($result) {
  displayTabInformation();
} else {

}

llxFooter();

function displayTabInformation()
{
  global $societe;
  global $langs;
  global $product;
  global $extralabels;
  global $db;

  $head = societe_prepare_head($societe);
  dol_fiche_head($head, 'consignes', $langs->trans("ThirdParty"), -1, 'company');

  $query = $db->query('SELECT * FROM llx_consignes_consigne WHERE fk_soc = ' . $societe->id);
  print '<table class="liste">
			<thead>
				<tr class="liste_titre">
					<th class="liste_titre">#</th>
					<th class="liste_titre">' . $langs->trans('Produit') . '</th>
					<th class="liste_titre">' . $langs->trans('Date') . '</th>
					<th class="liste_titre">' . $langs->trans('Qté') . '</th>
				</tr>
			</thead>
			<tbody>';
  if ($query) {
    $num = $db->num_rows($query);
    $i = 0;
    if ($num) {
      while ($i < $num) {
        $consigne = $db->fetch_object($query);
        $product->fetch($consigne->fk_product);
        $product->fetch_optionals($consigne->fk_product, $extralabels);
        print '<tr>';
        if ($consigne) {
          // You can use here results
          print '<td class="liste_titre">' . $consigne->rowid . '</td>';
          print '<td class="liste_titre">' . $product->getNomUrl(1) . '</td>';
          print '<td class="liste_titre">' . $consigne->tms . '</td>';
          print '<td class="liste_titre">' . $consigne->qty . '</td>';
        }
        $i++;
        print '</tr>';
      }
    }
  }
  print '</tbody></table>';
}

function displayListInformation()
{

}