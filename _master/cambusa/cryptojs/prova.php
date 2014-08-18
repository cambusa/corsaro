<html>

<script src="rollups/sha1.js"></script>
<script>
    var hash = CryptoJS.SHA1("Goniometrico[(@#)]");
    function init(){
        target.innerHTML=hash;
    }
</script>

<body onload="init()">

<?php
    print sha1("Goniometrico[(@#)]");
    print "<br>";
    print "<br>";
?>
<div id="target"><div>

</body>
</html>
