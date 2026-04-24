<?php
namespace ImoGrifo;


final class Seeds
{
public function seed(): void
{
// TIPOS padrão
foreach (['Residencial','Condomínio','Comercial','Misto','Loteamento'] as $t) {
if (! term_exists($t, 'tipo')) {
wp_insert_term($t, 'tipo', ['slug' => sanitize_title($t)]);
}
}


// STATUS padrão
foreach (['Lançamento','Em obras','Pronto'] as $s) {
if (! term_exists($s, 'status')) {
wp_insert_term($s, 'status', ['slug' => sanitize_title($s)]);
}
}


// ESTADOS (UF)
$ufs = ['AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT','PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO'];
foreach ($ufs as $uf) {
if (! term_exists($uf, 'estado')) {
wp_insert_term($uf, 'estado', ['slug' => strtolower($uf)]);
}
}
}
}