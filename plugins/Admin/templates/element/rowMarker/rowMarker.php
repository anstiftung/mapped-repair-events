<?php
declare(strict_types=1);

echo '<td style="text-align: center;">';
if ($show) {
    echo '<input ' . (isset($id) ? 'id="row-marker-' . $id . '"' : '') . ' type="checkbox" class="row-marker" />';
}
echo '</td>';
?>