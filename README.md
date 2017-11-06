# site-meta

Site header/footer meta tag dan tag default lainnya. Modul ini menyediakan service
`meta` yang bisa di panggil dari template dengan perintah `$this->meta->{method}`.

Untuk saat ini, service meta menyediakan dua method yaitu `head`, `foot` dan `schemaOrganization`.

Method `schemaOrganization` akan menggembalikan schema standar organization yang
pada umumnya digunakan untuk membentuk site schema.