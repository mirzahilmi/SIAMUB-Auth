<?php
use MirzaHilmi\SIAMUBAuth;

$user = SIAMUBAuth::authenticate('22515xxxxxxxxxx', 'xxxxxxxx');

$data = $user->getInformation();
echo $data['nim']; // 22515xxxxxxxxxx

echo $data['nim']; // 22515xxxxxxxxxx
echo $data['nama']; // Pemuja GKM
echo $data['jenjang']; // S1
echo $data['fakultas']; // Ilmu Komputer
echo $data['jurusan']; // Teknologi Informasi
echo $data['program_studi']; // Teknologi Informasi
echo $data['seleksi']; // Mandiri
echo $data['nomor_ujian']; // 123456789
echo $data['status']; // true