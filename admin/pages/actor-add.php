<?php
/**
 * DVD Profiler Liste - Admin: Neuer Schauspieler (Quick Add)
 * 
 * Schnelles Hinzufügen ohne Upload - für Masse-Eingabe
 * 
 * @package    dvdprofiler.liste
 * @version    1.5.0
 * @author     René Neuhaus
 */

// Redirect zu actor-edit.php (ohne ID = Neuer Actor)
header('Location: actor-edit.php');
exit;