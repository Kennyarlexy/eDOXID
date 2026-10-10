<?php
    function badge_class($status)
    {
        $class = '';
        if ($status === 'pending') {
            $class = 'bg-secondary';
        } else if ($status === 'processed') {
            $class = 'bg-warning';
        } else if ($status === 'rejected' || $status == 'inactive' || $status == 'repeated') {
            $class = 'bg-danger';
        } else if ($status === 'approved' || $status == 'active' || $status === 'approved_with_fixed') {
            $class = 'bg-success';
        }

        return $class;
    }

    function class_by_status($status)
    {
        $class = '';
        if ($status === 'pending') {
            $class = 'secondary';
        } else if ($status === 'processed') {
            $class = 'warning';
        } else if ($status === 'rejected' || $status == 'inactive' || $status == 'repeated') {
            $class = 'danger';
        } else if ($status === 'approved' || $status == 'active' || $status === 'approved_with_fixed') {
            $class = 'success';
        }

        return $class;
    }

    function status_english_to_bahasa($status)
    {
        if ($status == 'approved') {
            $status = 'Disetujui';
        } else if ($status == 'pending') {
            $status = 'Pending';
        } else if ($status == 'processed') {
            $status = 'Sedang Proses';
        } else if ($status == 'repeated') {
            $status = 'Diulang';
        } else if ($status == 'active') {
            $status = 'Aktif';
        } else if ($status == 'inactive') {
            $status = 'Non Aktif';
        } else if ($status == 'rejected') {
            $status = 'Gugur/Ditolak';
        }

        return $status;
    }

    function result_class_by_status($status)
    {
        $class = '';
        if ($status == 'pending') {
            $class = 'secondary';
        } else if ($status == 'repeated') {
            $class = 'warning';
        } else if ($status == 'rejected') {
            $class = 'danger';
        } else if ($status == 'approved' || $status == 'approved_with_fixed') {
            $class = 'success';
        }

        return $class;
    }

    function result_status_english_to_bahasa($status)
    {
        if ($status == 'approved') {
            $status = 'Diterima';
        } else if ($status == 'approved_with_fixed') {
            $status = 'Diterima dengan Perbaikan';
        } else if ($status == 'pending') {
            $status = 'Menunggu Keputusan';
        } else if ($status == 'repeated') {
            $status = 'Diulang';
        } else if ($status == 'rejected') {
            $status = 'Gugur/Ditolak';
        }

        return $status;
    }

    function translateDayToIndonesian($day_index)
    {
        if ($day_index == 0) {
            return 'Minggu';
        } else if ($day_index == 1) {
            return 'Senin';
        } else if ($day_index == 2) {
            return 'Selasa';
        } else if ($day_index == 3) {
            return 'Rabu';
        } else if ($day_index == 4) {
            return 'Kamis';
        } else if ($day_index == 5) {
            return 'Jumat';
        } else if ($day_index == 6) {
            return 'Sabtu';
        }
    }

    function translateMonthToIndonesian($month_index)
    {
        if ($month_index == 1) {
            return "Januari";
        } else if ($month_index == 2) {
            return "Februari";
        } else if ($month_index == 3) {
            return "Maret";
        } else if ($month_index == 4) {
            return "April";
        } else if ($month_index == 5) {
            return "Mei";
        } else if ($month_index == 6) {
            return "Juni";
        } else if ($month_index == 7) {
            return "Juli";
        } else if ($month_index == 8) {
            return "Agustus";
        } else if ($month_index == 9) {
            return "September";
        } else if ($month_index == 10) {
            return "Oktober";
        } else if ($month_index == 11) {
            return "November";
        } else if ($month_index == 12) {
            return "Desember";
        }
    }
?>
