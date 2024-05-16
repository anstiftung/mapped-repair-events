<tr>
    <td style="font-weight:bold;font-size:18px;padding-bottom:10px;">
        <p>
            <?php
                if (!isset($data)) {
                    echo 'Hallo,';
                } else {
                    echo 'Hallo ' . $data->firstname . ',';
                }
            ?>
        </p>
    </td>
</tr>
