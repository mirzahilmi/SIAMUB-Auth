<?php

namespace SIAMUBAuth\Models;

/**
 * Mahasiswa Class
 *
 * @property string $pasFoto
 * @property string $nim
 * @property string $nama
 * @property string $jenjang
 * @property string $fakultas
 * @property string $departemen
 * @property string $jurusan
 * @property string $programStudi
 * @property string $seleksi
 * @property string $nomorUjian
 * @property bool $status
 */
class Mahasiswa
{
    public $pasFoto;
    public $nim;
    public $nama;
    public $jenjang;
    public $fakultas;
    public $departemen;
    public $jurusan;
    public $programStudi;
    public $seleksi;
    public $nomorUjian;
    public $status;

    public function __construct(array $data)
    {
        $this->populate($data);
    }

    /**
     * Populate the Mahasiswa object with data.
     *
     * @param array $raw The raw data.
     */
    private function populate(array $raw): void
    {
        $this->nim = $raw[0];

        if (isset($raw[8])) {
            preg_match('/url\((.*?)\)/', $raw[8], $match);
            $this->pasFoto = $match[1];
        } else {
            $this->pasFoto = sprintf('https://siakad.ub.ac.id/dirfoto/foto/foto_20%s/%s.jpg', substr($this->nim, 0, 2), $this->nim);
        }

        $this->nama = $raw[1];

        $jenjangFakultas = explode('/', str_replace('Jenjang/Fakultas', '', $raw[2]));
        $this->jenjang = $jenjangFakultas[0];
        $this->fakultas = $jenjangFakultas[1];

        $this->jurusan = str_replace('Jurusan', '', $raw[3]);

        $this->departemen = $this->determineDepartment($this->jurusan);

        $this->programStudi = str_replace('Program Studi', '', $raw[4]);

        $this->seleksi = 'Seleksi' . str_replace('Seleksi', '', $raw[5]);

        $this->nomorUjian = preg_replace('/\D/', '', $raw[6]);

        $this->status = trim(str_replace('Status : ', '', $raw[7]));
    }

    /**
     * Determine the department based on the major.
     *
     * @param string $major The student's major.
     * @return string The determined department.
     */
    private function determineDepartment(string $major): string
    {
        switch ($major) {
            case 'Teknologi Informasi':
            case 'Sistem Informasi':
            case 'Pendidikan Teknologi Informasi':
                return 'Sistem Informasi';
            case 'Teknik Komputer':
            case 'Teknik Informatika':
                return 'Teknik Informatika';
        }

        return '';
    }
}
