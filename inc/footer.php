<?php

/**
 * @author Dan Verständig
 */

$db->__destruct();

?>
<div id="footer">
    <p>
    <?php
    if (isset($_GET['position']) && strlen($_GET['position'])) {
    ?>
        <a href="javascript:history.back();"><?php echo TEXT_BACK;?></a>
    <?php
    }
    ?>
    </p>
</div>
</div>
</body>
</html>