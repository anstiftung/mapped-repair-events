<?php
$data = json_decode($object->data);
?>

<iframe srcdoc='<?php echo $data->settings->htmlMessage; ?>' style="width: 770px; height: 600px; border: none;"></iframe>