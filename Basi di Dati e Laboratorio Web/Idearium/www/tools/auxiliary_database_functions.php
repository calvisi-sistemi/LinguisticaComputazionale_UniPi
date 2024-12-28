<?php
/**
 * Prende in input un array e restituisce un array associativo dove ad ogni elemento dell'array (valore) è fatto corrispondere un
 * placeholder (chiave) da usare nella costruzione delle query SQL.
 * Ad esempio:
 * $values = [a, b, c];
 * $placeholder_prefix = 'lettera';
 * Daranno: 
 * [
 *  ':lettera_0' => 'a',
 *  ':lettera_1' => 'b',
 * ...
 * ]
 * @param array $values Array da cui partire
 * @param string $placeholder_prefix Prefisso del placeholder. Valore di default: 'placeholder'
 * @return array Array associativo PLACEHOLDER => VALORE
 */
function build_placeholder_to_value_from_an_array(array $values, string $placeholder_prefix = "placeholder"): array
{
  $placeholders_to_value = [];
  foreach ($values as $index => $value) {
    $placeholder = ":{$placeholder_prefix}_{$index}";
    $placeholders_to_value[$placeholder] = $value;
  }
  return $placeholders_to_value;
}