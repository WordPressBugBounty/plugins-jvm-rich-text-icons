<?php
$prefix_class = JVM_Richtext_icons::get_class_prefix();
?>
i.<?php echo $prefix_class;?> {
    width: 1em;
    display: inline-block;
    height: 1em;
    position: relative;
    white-space: break-spaces;
    line-height: 1;
}
i.<?php echo $prefix_class;?>:after {
    content: '';    
    position: absolute;
    height: 100%;
    width: 100%;
    top: 0;
    left: 0;
    background-color: currentColor; 
    mask-repeat: no-repeat; 
    mask-repeat: no-repeat;
    -webkit-mask-repeat: no-repeat;
    mask-size:contain;
    -webkit-mask-size:contain;
    mask-position: 50% 50%;
    -webkit-mask-position: 50% 50%;
}
<?php 
    foreach ($files as $file) {
        $pi = pathinfo($file);
        
        $icon_class = sanitize_title($pi['filename']);
        $file_content = file_get_contents($file);
?>
i.<?php echo $prefix_class;?>.<?php echo $icon_class;?>:after {
    --icon-bg: url("data:image/svg+xml;base64,<?php echo base64_encode($file_content);?>");
    -webkit-mask-image: var(--icon-bg);
    mask-image: var(--icon-bg);
}
<?php } ?>