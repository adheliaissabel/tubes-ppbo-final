<?php
// Interface untuk memenuhi kriteria OOP
interface LaporanInterface {
    public function readLaporan($start_date, $end_date);
    public function exportToPDF(); 
}
?>