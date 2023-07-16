<?php

namespace MirzaHilmi\Models;

class Mahasiswa
{
	public string $pasFoto;
	public string $nim;
	public string $nama;
	public string $jenjang;
	public string $fakultas;
	public string $departemen;
	public string $jurusan;
	public string $programStudi;
	public string $seleksi;
	public string $nomorUjian;
	public bool $status;

	public function __construct(array $data)
	{
		$this->populate($data);
	}

	private function populate(array $raw): void
	{
		$this->nim = $raw[0];

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
