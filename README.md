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
✅ Login to the SIAM UB Authentication system using provided credentials. <br>
✅ Scrape user information, such as name, nim, department and others, from the authenticated user profile. <br>
⬜ Retrieve course information, including course codes, names, and schedules. **Not supported yet.**

## Requirements
To run this project, you need to have the following requirements installed on your system:
- PHP (version 7.2.5 or 8.3 and below)
- Guzzle HTTP library
- Composer

## Installation
You can install the SIAM UB Authentication package via Composer. Run the following command in your terminal:
```bash
composer require mirzahilmi/siamub-auth
```

## Usage
1. Import and Instantiate the SIAMUBAuth class with your credentials:
```php
<?php
use MirzaHilmi\SIAMUBAuth;

$user = SIAMUBAuth::authenticate('22515xxxxxxxxxx', 'xxxxxxxx');
```
2. Retrieve user information:
```php
$data = $user->getInformation();
echo $data['nim']; // 22515xxxxxxxxxx
```
3. Available user informations:
```php
echo $data['nim']; // 22515xxxxxxxxxx
echo $data['nama']; // Pemuja GKM
echo $data['jenjang']; // S1
echo $data['fakultas']; // Ilmu Komputer
echo $data['jurusan']; // Teknologi Informasi
echo $data['program_studi']; // Teknologi Informasi
echo $data['seleksi']; // Mandiri
echo $data['nomor_ujian']; // 123456789
echo $data['status']; // true
```

**Important:** Remember to use this project responsibly and only on systems for which you have proper authorization.

## Contributing
Contributions to this project are welcome. If you encounter any issues or have ideas for improvements, feel free to open an issue or submit a pull request.

## License
This project is licensed under the [GPL-3.0 License](LICENSE).