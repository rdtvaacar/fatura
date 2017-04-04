<?php

namespace Acr\Fat\Model;

use Illuminate\Database\Eloquent\Model;
use App\User;
use Auth;
use Acr\Fat\Model\Fatura_ayar_model;

class Fatura_model extends Model

{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'fatura';
    public    $uye_id;
    public    $kurum_id;

    function uye_id()
    {
        if (Auth::check()) {
            $ayar = self::fatura_ayar();
            if (empty($ayar)) {
                return 1;
            } else {
                $stun = $ayar->user_id_stun;
                return Auth::user()->$stun;
            }
        } else {
            return 1;
        }
    }

    function tab_menu()
    {
        $data = [
            'fatura_gelen' => ['Gelen Kutusu', 'inbox', 0],
            'fatura_giden' => ['Gönderilenler', 'envelope-o', 1],
            'fatura_cop'   => ['Çöp Kutusu', 'trash-o', 2],
        ];
        return $data;
    }

    function kurum_id()
    {
        if (Auth::check()) {
            return $this->kurum_id = Auth::user()->kurum_id;
        } else {
            return $this->kurum_id = 0;
        }
    }

    function gelen_okunmayan_sayi($tur)
    {
        return Fatura_users_model::where('fatura_users.uye_id', self::uye_id())->where('okundu', 0)->where('tur', $tur)->where('sil', 0)->count();
    }

    function mesajlar($tab, $sil)
    {
        $data    = self::tab_menu();
        $tur     = $data[$tab][2];
        $ayar    = Fatura_model::fatura_ayar();
        $user_id = $ayar->user_id_stun;
        return $sorgu = Fatura_users_model::leftJoin('fatura', 'fatura_users.mesaj_id', '=', 'fatura.id')
            ->leftJoin('users', 'users.' . $user_id, '=', 'fatura_users.gon_id')
            ->where('fatura_users.uye_id', self::uye_id())
            ->where('fatura_users.tur', $tur)
            ->where('fatura_users.sil', $sil)
            ->orderBy('fatura.id', 'desc')
            ->select('fatura.*', 'users.*', 'fatura_users.*', 'users.' . $user_id . ' as uye_id', 'fatura.id as fatura_id', 'fatura_users.id as fatura_users_id', 'users.created_at as users_cd', 'fatura.created_at as d_cd', 'fatura_users.created_at as du_cd')
            ->paginate(50);
    }

    function mesaj_oku($mesaj_id)
    {
        $ayar    = Fatura_model::fatura_ayar();
        $user_id = $ayar->user_id_stun;
        Fatura_users_model::where('uye_id', self::uye_id())->where('id', $mesaj_id)->update(['okundu' => 1]);
        return Fatura_users_model::leftJoin('fatura', 'fatura_users.mesaj_id', '=', 'fatura.id')
            ->leftJoin('users', 'users.' . $user_id, '=', 'fatura_users.gon_id')
            ->leftJoin('fatura_dosya', 'fatura_dosya.mesaj_id', '=', 'fatura_users.mesaj_id')
            ->where('fatura_users.uye_id', self::uye_id())
            ->where('fatura_users.id', $mesaj_id)
            ->select('fatura_dosya.*', 'fatura.*', 'users.*', 'fatura_users.*', 'users.' . $user_id . ' as uye_id', 'fatura_dosya.id as fatura_dosya_id', 'fatura.id as fatura_id', 'fatura_users.id as fatura_users_id', 'users.created_at as users_cd', 'fatura.created_at as d_cd', 'fatura_users.created_at as du_cd')
            ->first();
    }

    function dosyalar($mesaj_id)
    {
        $dosyaSorgu = Fatura_dosya_model::where('mesaj_id', $mesaj_id);
        $dosya_sayi = $dosyaSorgu->count();
        if ($dosya_sayi > 0) {
            return $dosya = $dosyaSorgu->get();
        } else {
            $dosya = [];
            return (object)$dosya;
        }
    }

    function tum_dosya_sil($mesaj_id)
    {
        $dosyalar = self::dosyalar($mesaj_id);
        foreach ($dosyalar as $item) {
            if (file_exists('/uploads/' . $item->dosya_isim)) {
                unlink('/uploads/' . $item->dosya_isim);
            }
        }
        Fatura_dosya_model::where('mesaj_id', $mesaj_id)->delete();

    }

    function sil($fatura_id)
    {
        $fatura_user_sorgu = Fatura_users_model::where('uye_id', self::uye_id())->whereIn('id', $fatura_id);
        $fatura_user_sorgu->update(['tur' => 2, 'sil' => 1]);
        $fatura_user_satir = $fatura_user_sorgu->first();
        self::tum_dosya_sil($fatura_user_satir->mesaj_id);
    }

    function cope_tasi($fatura_id)
    {
        Fatura_users_model::where('uye_id', self::uye_id())->whereIn('id', $fatura_id)->update(['tur' => 2]);
    }

    function tek_sil($fatura_id)
    {
        Fatura_users_model::where('uye_id', self::uye_id())->where('id', $fatura_id)->update(['tur' => 2, 'sil' => 1]);
    }

    function tek_cope_tasi($fatura_id)
    {
        Fatura_users_model::where('uye_id', self::uye_id())->where('id', $fatura_id)->update(['tur' => 2]);
    }

    function gonderen($gon_id)
    {
        $ayar     = Fatura_model::fatura_ayar();
        $user     = new User();
        $gonderen = $user->where($ayar->user_id_stun, $gon_id)->first()->name;
        return $gonderen;
    }

    function alan($uye_id)
    {
        $ayar = Fatura_model::fatura_ayar();
        $user = new User();
        $alan = $user->where($ayar->user_id_stun, $uye_id)->first();
        return $alan;
    }

    function fatura_mesaj_kaydet($konu, $mesaj, $uye_id, $gon_id)
    {
        $data = [
            'konu'  => $konu,
            'mesaj' => $mesaj
        ];

        $mesaj_id = Fatura_model::insertGetId($data);
        $data2    = [
            'uye_id'   => $uye_id,
            'mesaj_id' => $mesaj_id,
            'gon_id'   => $gon_id,
            'tur'      => 0
        ];

        Fatura_users_model::insert($data2);
        $data3 = [
            'uye_id'   => $gon_id,
            'mesaj_id' => $mesaj_id,
            'gon_id'   => $uye_id,
            'tur'      => 1,
            'okundu'   => 1
        ];
        Fatura_users_model::insert($data3);
        return $mesaj_id;
    }

    function fatura_dosya_kaydet($mesaj_id, $dosya_isim, $uye_id, $gon_id, $size, $type, $isim)
    {

        $data = [
            'dosya_org_isim' => $isim,
            'dosya_isim'     => $dosya_isim,
            'mesaj_id'       => $mesaj_id,
            'uye_id'         => $uye_id,
            'gon_id'         => $gon_id,
            'size'           => $size,
            'type'           => $type
        ];
        Fatura_dosya_model::insert($data);

    }

    function fatura_ayar()
    {
        return Fatura_ayar_model::first();
    }

    function fatura_ayar_kaydet($data)
    {
        unset($data['_token']);
        if (Fatura_ayar_model::count() > 0) {
            return Fatura_ayar_model::where('id', 1)->update($data);
        } else {
            return Fatura_ayar_model::insert($data);
        }

    }
}
