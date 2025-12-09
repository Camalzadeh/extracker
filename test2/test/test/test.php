<?php

$salamlama = "Salam, dünya! PHP işləyir!";
$hesablama = 5 * 8;

?>
<!DOCTYPE html>
<html lang="az">
<head>
    <meta charset="UTF-8">
    <title>PHP Test Səhifəsi</title>
</head>
<body>

<h1>PHP Kodunun Nəticəsi</h1>

<?php
// PHP dəyişənini HTML içində ekrana çıxarmaq
echo "<p>PHP-dən mesaj: <b>" . $salamlama . "</b></p>";
?>

<p>Sadə bir riyazi hesablama:
    <?php
    // Hesablama nəticəsini ekrana çıxarmaq
    echo "5 vur 8 bərabərdir: <b>" . $hesablama . "</b>";
    ?>
</p>

<h2>PHP Versiyası (Düzgün işləyib-işləmədiyini yoxlamaq üçün):</h2>
<?php
echo phpversion();
echo phpinfo();
?>

</body>
</html>