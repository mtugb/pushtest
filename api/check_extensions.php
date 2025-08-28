<?php
if (extension_loaded('openssl')) {
    echo "openssl extension is loaded.\n";
} else {
    echo "openssl extension is NOT loaded.\n";
}

if (extension_loaded('gmp')) {
    echo "gmp extension is loaded.\n";
} else {
    echo "gmp extension is NOT loaded.\n";
}
?>