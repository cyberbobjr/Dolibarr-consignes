<?php
    // Load Dolibarr environment
    $res = 0;
    // Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
    if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"]))
        $res = @include($_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php");
    // Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
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

    require_once(DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php');
    require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';
    require_once DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php';
    require_once('./pivotTable.php');

    dol_include_once('/consignes/class/consigne.class.php');
    dol_include_once('/expedition/class/expedition.class.php');

    // Load translation files required by the page
    $langs->loadLangs(["consignes@consignes",
                       "other"]);

    // Set title
    $help_url = '';
    $title = $langs->trans('SynthesisConsigne');
    $societeObject = new Societe($db);

    // get ORDER BY info
    $contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'consignesynthese';   // To manage different context of search

    $order = GETPOST('order');
    $sort = GETPOST('sort');
    $sort = empty($sort) ? 'SORT_ASC' : $sort;
    // Start page
    llxHeader('', $title, $help_url);
    $datas = getSumConsignes($db);
    if (count($datas) == 0) {
        print '<h1>Aucune consignes enregistrées</h1>';
    } else {
        $pivotDatas = parseDataToPivotTable($datas);
        $headers = extractHeader($pivotDatas);
        $total = extractTotalData($pivotDatas);
        if (!empty($order)) {
            $volume = array_column($pivotDatas, $headers[$order]);
            array_multisort($volume, ($sort == 'SORT_ASC') ? SORT_ASC : SORT_DESC, $pivotDatas);
        }
        print '<style>th:first-child, td:first-child {border-right-style: solid; border-right-width: thin;background-color: rgba(0,0,0,0.10);}</style>';
        print '<style>th:not(:first-child), td:not(:first-child) {text-align: center;width:20%; max-width: 0}</style>';
        print '<div class="div-table-responsive">';
        print '<table class="tagtable liste">';
        print '<tbody>';
        print '<tr class="liste_titre">';
        foreach ($headers as $key => $header) {
            if ($key == 0) // _id
                continue;
            if ($key == 1) { // client
                print '<th style="min-width: 350px;">' . $header . '</th>';
                continue;
            }
            $sortUrl = ($sort == 'SORT_DESC') ? 'SORT_ASC' : 'SORT_DESC';
            $sortIcon = ($sort == "SORT_ASC") ? "up" : "down";
            $sortHtml = (!empty($order) && $order == $key) ? '&nbsp;<i class="fa fa-sort-' . $sortIcon . '"></i>' : '';
            print '<th><a href="consigne_synthese.php?order=' . $key . '&sort=' . $sortUrl . '">' . str_replace('__qty', '',
                                                                                                                $header) . '</a>' . $sortHtml . '</th>';
        }
        print '<th>' . $langs->trans('Date dernière expédition') . '</th>';
        print '</tr>';
        foreach ($pivotDatas as $pivotData) {
            print '<tr>';
            foreach ($pivotData as $key => $value) {
                if ($key == '_id')
                    continue;

                if ($key == 'SocieteRowId' && $value != 'TOT') {
                    $societeObject->fetch($value);
                    print '<td>' . $societeObject->getNomUrl() . '</td>';
                    continue;
                }
                print '<td>' . $value . '</td>';
            }
            print '<td>' . getLastShipping($db, $pivotData['SocieteRowId']) . '</td>';
            print '</tr>';
        }
        print '<tfooter><tr>';
        foreach ($total as $key => $value) {
            if ($key == '_id')
                continue;
            print '<td>' . $value . '</td>';
        }
        print '</tr></tfooter>';
        print '</tbody></table>';
        print '</div>';
    }
    llxFooter();

    function getLastShipping($db, $societe_rowid)
    {
        if (empty($societe_rowid)) {
            return NULL;
        }
        $consigneObject = new Consigne($db);
        $consigneObject->fetchBySocId($societe_rowid);
        $fk_shipping = $consigneObject->fk_shipping;
        if (empty($fk_shipping)) {
            return NULL;
        }
        $shippingObject = new Expedition($db);
        $shippingObject->fetch($fk_shipping);
        $date_delivery = DateTime::createFromFormat('U', $shippingObject->date_delivery);
        $date_delivery = ($date_delivery) ? $date_delivery->format('d/m/Y H:m:i') : '';
        $html = $shippingObject->getNomUrl();
        $html .= '<br/>' . $date_delivery;
        return $html;
    }

    function getSumConsignes(DoliDBMysqli $db)
    {
        $results = [];
        $sql = "SELECT SUM(" . MAIN_DB_PREFIX . "consignes_consigne.qty) as qty, " . MAIN_DB_PREFIX . "product.ref, " . MAIN_DB_PREFIX . "product.label, " . MAIN_DB_PREFIX . "societe.rowid as SocieteRowId,CONCAT(" . MAIN_DB_PREFIX . "societe.nom,' ', " . MAIN_DB_PREFIX . "societe.name_alias) as Client, " . MAIN_DB_PREFIX . "societe.code_client";
        $sql .= " FROM " . MAIN_DB_PREFIX . "consignes_consigne";
        $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "product ON (" . MAIN_DB_PREFIX . "consignes_consigne.fk_product=" . MAIN_DB_PREFIX . "product.rowid)";
        $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "societe ON (" . MAIN_DB_PREFIX . "consignes_consigne.fk_soc=" . MAIN_DB_PREFIX . "societe.rowid)";
        $sql .= " GROUP BY " . MAIN_DB_PREFIX . "societe.rowid, fk_product";
        $resql = $db->query($sql);
        if ($resql) {
            $num = $db->num_rows($resql);
            $i = 0;
            if ($num) {
                while ($i < $num) {
                    $results[] = $db->fetch_array($resql);
                    $i++;
                }
            }
        }
        return $results;
    }

    /**
     * @param $datas
     * @return array
     */
    function parseDataToPivotTable($datas)
    {
        $pivotTable = Pivot::factory($datas)
                           ->pivotOn(['SocieteRowId'])
                           ->addColumn(['label'], ['qty'])
                           ->fullTotal()
                           ->fetch();
        return $pivotTable;
    }

    function extractHeader($pivotDatas)
    {
        $langs = $GLOBALS['langs'];
        $row = $pivotDatas[0];
        $keys = array_keys($row);
        // purge __ pour toutes les clefs
        foreach ($keys as $key => $value) {
            //$keys[$key] = str_replace('__qty', '', $value);
            if ($value == 'SocieteRowId') {
                $keys[$key] = $langs->trans('Client');
            }
        }
        return $keys;
    }

    function extractTotalData(&$pivotDatas)
    {
        $total = [];
        foreach ($pivotDatas as $key => $value) {
            if ($value['SocieteRowId'] == 'TOT') {
                $total = $pivotDatas[$key];
                unset($pivotDatas[$key]);
            }
        }
        return $total;
    }

?>
