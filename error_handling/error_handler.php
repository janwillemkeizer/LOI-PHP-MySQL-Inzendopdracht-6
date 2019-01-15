<?php

function error_handler ($error_type, $error_msg, $error_file, $error_line)
{
  echo "<h3>Fout!</h3>";
  echo "<p>Sorry, maar er is iets mis gegaan.</p>";
  echo "<p>De fout heeft $error_type als typenummer.</p>";
  echo "Dit is de foutmelding: $error_msg</p>";
  echo "<p>Het is gebeurd in dit bestand: $error_file op regel $error_line</p>";
} 

?>

