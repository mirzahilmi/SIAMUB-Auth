# SIAM UB Authentication

This repository contains a project for educational purposes focused on SIAM UB Authentication, which is a web authentication system used by Faculty of Computer Science at Brawijaya University. The project utilizes web scraping techniques to retrieve information from the SIAM UB Authentication system. The implementation is done using PHP and the Guzzle HTTP library.

**Disclaimer:** <br>
Please note that this project is intended solely for educational purposes and to demonstrate web scraping techniques. It should not be used for any malicious activities or unauthorized access to systems. The developers of this project are not responsible for any misuse or illegal actions undertaken by users.

## Table of Contents
- [Introduction](#introduction)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Contributing](#contributing)
- [License](#license)

## Introduction
The SIAM UB Authentication project aims to retrieve information from the SIAM UB Authentication system, which is used for user authentication. The project demonstrates how web scraping techniques can be employed to automate the retrieval of data from web pages.

## Features
- [✅] Login to the SIAM UB Authentication system using provided credentials.
- [✅] Scrape user information, such as name, nim, department, profile picture and others, from the authenticated user profile.
- [⬜] Retrieve course information, including course codes, names, and schedules. **Not supported yet.**

## Prerequisite
- [PHP](https://www.php.net/): version 7.3 or higher
- [Guzzle](https://docs.guzzlephp.org/en/stable/): version 6 or higher
- [Composer](https://getcomposer.org/)

## Installation
You can install the SIAM UB Authentication package via Composer. Run the following command in your terminal:
```bash
composer require mirzahilmi/siamub-auth
```

## Usage
1. Authenticate with user credentials using SIAMUBAuth::authenticate() method:
```php
<?php
use SIAMUBAuth\SIAMAuth;
use GuzzleHttp\Client;

$client = new Client();
$user = SIAMAuth::authenticate('22515xxxxxxxxxx', 'xxxxxxxx', $client);

echo get_class($user); // SIAMUBAuth\Models\Mahasiswa
```
2. Get user information:
```php
echo $user->nim; // 22515xxxxxxxxxx
```
3. Available user informations:
```php
echo $user->pasFoto;      // https://admisi.ub.ac.id/upload/**/*.jpg
echo $user->nim;          // 22515xxxxxxxxxx
echo $user->nama;         // Pemuja GKM
echo $user->jenjang;      // S1
echo $user->fakultas;     // Ilmu Komputer
echo $user->departemen;   // Sistem Informasi
echo $user->jurusan;      // Teknologi Informasi
echo $user->programStudi; // Teknologi Informasi
echo $user->seleksi;      // Seleksi Mandiri Brawijaya - xxxxx
echo $user->nomorUjian;   // 123456789
echo $user->status;       // 1
```

**Important:** Remember to use this project responsibly and only on systems for which you have proper authorization.

## Contributing
Contributions to this project are welcome. If you encounter any issues or have ideas for improvements, feel free to open an issue or submit a pull request.

## License
This project is licensed under the [MIT License](LICENSE).