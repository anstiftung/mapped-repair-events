<?php
declare(strict_types=1);

echo $this->element('funding/status/usageproofStatus', [
    'funding' => $funding,
    'additionalTextBefore' => '',
]);
?>

<p style="margin-bottom:20px;">
    Sachbericht und Belegliste müssen korrekt ausgefüllt werden, bis beide Blöcke grün sind.<br />
    Anschließend wird der Verwendungsnachweis von einem Admin bestätigt.
</p>