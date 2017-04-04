<?php

namespace Acr\Des\Model;

use Illuminate\Database\Eloquent\Model;
use App\User;
use Auth;
use Acr\Des\Model\Destek_ayar_model;

class Destek_model extends Model

{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'destek';
    public    $uye_id;
    public    $kurum_id;

    function uye_id()
    {
        if (Auth::check()) {
            $ayar = self::destek_ayar();
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
            'destek_gelen' => ['Gelen Kutusu', 'inbox', 0],
            'destek_giden' => ['Gönderilenler', 'envelope-o', 1],
            'destek_cop'   => ['Çöp Kutusu', 'trash-o', 2],
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
        return Destek_users_model::where('destek_users.uye_id', self::uye_id())->where('okundu', 0)->where('tur', $tur)->where('sil', 0)->count();
    }

    function mesajlar($tab, $sil)
    {
        $data    = self::tab_menu();
        $tur     = $data[$tab][2];
        $ayar    = Destek_model::destek_ayar();
        $user_id = $ayar->user_id_stun;
        return $sorgu = Destek_users_model::leftJoin('destek', 'destek_users.mesaj_id', '=', 'destek.id')
            ->leftJoin('users', 'users.' . $user_id, '=', 'destek_users.gon_id')
            ->where('destek_users.uye_id', self::uye_id())
            ->where('destek_users.tur', $tur)
            ->where('destek_users.sil', $sil)
            ->orderBy('destek.id', 'desc')
            ->select('destek.*', 'users.*', 'destek_users.*', 'users.' . $user_id . ' as uye_id', 'destek.id as destek_id', 'destek_users.id as destek_users_id', 'users.created_at as users_cd', 'destek.created_at as d_cd', 'destek_users.created_at as du_cd')
            ->paginate(50);
    }

    function mesaj_oku($mesaj_id)
    {
        $ayar    = Destek_model::destek_ayar();
        $user_id = $ayar->user_id_stun;
        Destek_users_model::where('uye_id', self::uye_id())->where('id', $mesaj_id)->update(['okundu' => 1]);
        return Destek_users_model::leftJoin('destek', 'destek_users.mesaj_id', '=', 'destek.id')
            ->leftJoin('users', 'users.' . $user_id, '=', 'destek_users.gon_id')
            ->leftJoin('destek_dosya', 'destek_dosya.mesaj_id', '=', 'destek_users.mesaj_id')
            ->where('destek_users.uye_id', self::uye_id())
            ->where('destek_users.id', $mesaj_id)
            ->select('destek_dosya.*', 'destek.*', 'users.*', 'destek_users.*', 'users.' . $user_id . ' as uye_id', 'destek_dosya.id as destek_dosya_id', 'destek.id as destek_id', 'destek_users.id as destek_users_id', 'users.created_at as users_cd', 'destek.created_at as d_cd', 'destek_users.created_at as du_cd')
            ->first();
    }

    function dosyalar($mesaj_id)
    {
        $dosyaSorgu = Destek_dosya_model::where('mesaj_id', $mesaj_id);
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
        Destek_dosya_model::where('mesaj_id', $mesaj_id)->delete();

    }

    function sil($destek_id)
    {
        $destek_user_sorgu = Destek_users_model::where('uye_id', self::uye_id())->whereIn('id', $destek_id);
        $destek_user_sorgu->update(['tur' => 2, 'sil' => 1]);
        $destek_user_satir = $destek_user_sorgu->first();
        self::tum_dosya_sil($destek_user_satir->mesaj_id);
    }

    function cope_tasi($destek_id)
    {
        Destek_users_model::where('uye_id', self::uye_id())->whereIn('id', $destek_id)->update(['tur' => 2]);
    }

    function tek_sil($destek_id)
    {
        Destek_users_model::where('uye_id', self::uye_id())->where('id', $destek_id)->update(['tur' => 2, 'sil' => 1]);
    }

    function tek_cope_tasi($destek_id)
    {
        Destek_users_model::where('uye_id', self::uye_id())->where('id', $destek_id)->update(['tur' => 2]);
    }

    function gonderen($gon_id)
    {
        $ayar     = Destek_model::destek_ayar();
        $user     = new User();
        $gonderen = $user->where($ayar->user_id_stun, $gon_id)->first()->name;
        return $gonderen;
    }

    function alan($uye_id)
    {
        $ayar = Destek_model::destek_ayar();
        $user = new User();
        $alan = $user->where($ayar->user_id_stun, $uye_id)->first();
        return $alan;
    }

    function destek_mesaj_kaydet($konu, $mesaj, $uye_id, $gon_id)
    {
        $data = [
            'konu'  => $konu,
            'mesaj' => $mesaj
        ];

        $mesaj_id = Destek_model::insertGetId($data);
        $data2    = [
            'uye_id'   => $uye_id,
            'mesaj_id' => $mesaj_id,
            'gon_id'   => $gon_id,
            'tur'      => 0
        ];

        Destek_users_model::insert($data2);
        $data3 = [
            'uye_id'   => $gon_id,
            'mesaj_id' => $mesaj_id,
            'gon_id'   => $uye_id,
            'tur'      => 1,
            'okundu'   => 1
        ];
        Destek_users_model::insert($data3);
        return $mesaj_id;
    }

    function destek_dosya_kaydet($mesaj_id, $dosya_isim, $uye_id, $gon_id, $size, $type, $isim)
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
        Destek_dosya_model::insert($data);

    }

    function destek_ayar()
    {
        return Destek_ayar_model::first();
    }

    function destek_ayar_kaydet($data)
    {
        unset($data['_token']);
        if (Destek_ayar_model::count() > 0) {
            return Destek_ayar_model::where('id', 1)->update($data);
        } else {
            return Destek_ayar_model::insert($data);
        }

    }
}
