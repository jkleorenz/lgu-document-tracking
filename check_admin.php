<?php
$user = App\Models\User::where('email', 'admin@lgu.gov')->first();
if ($user) {
    echo "User found: " . $user->name . "\n";
    if (Hash::check('password', $user->password)) {
        echo "Password is default ('password')\n";
    } else {
        echo "Password has been changed\n";
    }
} else {
    echo "User not found\n";
}

$user2 = App\Models\User::where('email', 'bontoclgu@gmail.com')->first();
if ($user2) {
    echo "User found: " . $user2->name . "\n";
    // Checking if this one also has default password just in case
    if (Hash::check('password', $user2->password)) {
        echo "Password is default ('password')\n";
    } else {
        echo "Password is NOT default ('password')\n";
    }
}
