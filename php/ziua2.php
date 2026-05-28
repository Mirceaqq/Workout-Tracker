cat > php/ziua2.php << 'EOF'
<?php

// Ziua 2 - Workout Tracker
$numbers = [3, 7, 12, 5, 8, 21, 4, 19, 6, 11];

$pare = 0;
$impare = 0;

// Parcurgem fiecare element din array
for ($i = 0; $i < count($numbers); $i++) {

    // Verificam daca numarul este par sau impar
    if ($numbers[$i] % 2 == 0) {
        echo $numbers[$i] . " este par\n";
        $pare++;
    } else {
        echo $numbers[$i] . " este impar\n";
        $impare++;
    }

}

// Afisam rezultatele finale
echo "\n";
echo "Total numere: " . count($numbers) . "\n";
echo "Numere pare: " . $pare . "\n";
echo "Numere impare: " . $impare . "\n";

error_log("Ziua 2: script par/impar executat cu succes.");