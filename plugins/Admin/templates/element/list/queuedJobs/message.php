<?php
$data = json_decode($object->data);
?>

<iframe srcdoc='<?php echo $data->settings->htmlMessage; ?>' style="width: 800px; height: 500px; border: none;"></iframe>
