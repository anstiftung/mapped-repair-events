<tr>
    <td style="font-weight:bold;font-size:18px;padding-bottom:10px;">
        <p>
            <?php
                if ($data->is_company) {
                    echo  'Hallo ' . $data->firstname . ',';
                } else {
                    echo 'Hallo ' . $data->firstname . ' ' . $data->lastname . ',';
                }
            ?>
        </p>
    </td>
</tr>
