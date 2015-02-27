<?php

/**
 * @author Dan Verständig
 */

$db->__destruct();

?>
<div id="footer">
    <?php
    if (isset($_GET['position']) && strlen($_GET['position'])) {
        ?>
        <p>
            <a href="javascript:history.back();">zurück</a>
        </p><br/>
    <?php
    } else {
        ?>
        <br/><br/>
    <?php
    }
    ?>
</div>
</div>
</body>
</html>