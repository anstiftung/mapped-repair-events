<?php
use Cake\Core\Configure;

$pdf->setDefaults();

$html = '
<p>' .
    $funding->fundingsupporter->name . '<br />' .
    $funding->fundingsupporter->street . '<br />' .
    $funding->fundingsupporter->zip . ' ' . $funding->fundingsupporter->city .
'</p>';
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(15);

$html = '
<table border="1" cellpadding="0">
    <tr>
        <td align="left"><b>Förderbewilligung</b></td>
        <td align="right">München, ' . $formattedTimestamp . '</td>
    </tr>
</table>';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Ln(5);

$html = '
<p>
Sehr geehrte/r ' . $funding->owner_user->name . ',<br />
sehr geehrte/r ' . $funding->fundingsupporter->name . ',
</p>

<p>
wir freuen uns, Ihnen mitteilen zu können, dass die anstiftung eine Zuwendung gefördert vom Bundesministerium für 
Umwelt, Naturschutz, nukleare Sicherheit und Verbraucherschutz (BMUV) aufgrund eines Beschlusses des Deutschen Bundestages
in Höhe von <b>' . $this->MyNumber->formatAsDecimal($funding->budgetplan_total_with_limit) . ' €</b> Euro gewährt.
</p>

<p>
Die Mittel sind vorgesehen für Anschaffungen/Maßnahmen gemäß Antrag vom ' . $formattedTimestamp . '.
</p>

<p>
Die Fördersumme wird auf das Konto ' . $funding->fundingsupporter->iban . ' überwiesen.
</p>

<p>Die zugrundeliegenden
<a href="'.Configure::read('AppConfig.serverName') . $this->MyHtml->urlPageDetail('richtlinie').'">Förderrichtlinien</a>
sind verbindlich einzuhalten.
</p>

<p>
Der Empfänger stellt der anstiftung <b><u>unmittelbar nach Erhalt</b></u> der Zuwendung eine Zuwendungsbestätigung gemäß dem aktuell
gültigen amtlichen Muster (§ 10 b EStG) des Bundesministeriums der Finanzen über die erhaltene Fördersumme aus und lädt
sie im Förderportal hoch.
</p>
';
$pdf->writeHTML($html, true, false, true, false, '');

$pdf->Ln(10);

$html = '
<p>
Mit freundlichen Grüßen
</p>

<p>
anstiftung
</p>';

$pdf->writeHTML($html, true, false, true, false, '');
