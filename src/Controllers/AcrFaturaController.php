<?php

namespace Acr\Des\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Acr\Des\Model\Destek_model;
use Acr\Des\Model\Destek_dosya_model;
use Auth;
use Acr\Des\Controllers\MailController;

class AcrDesController extends Controller
{
    protected $basarili           = '<div class="alert alert-success">Başarıyla Eklendi</div>';
    protected $silindi            = '<div class="alert alert-warning">Başarıyla Silindi</div>';
    protected $dosyaBuyuk         = '<div class="alert alert-danger">Yüklemeye çalıştığınız dosyanın boyutu 20 MB\'den büyük</div>';
    protected $gonderildi         = '<div class="alert alert-success">Mesajınız başarıyla gönderildi, en kısa zamanda size yanıt vermeye çalışacağız, teşekkür ederiz.</div>';
    protected $basariliGuncelleme = '<div class="alert alert-success">Başarıyla Güncellendi</div>';

    function login(Request $request)
    {
        $destek_model = new Destek_model();
        if ($request->server('SERVER_NAME') == 'destek2') {
            Auth::loginUsingId(1, true);
            echo $destek_model->uye_id();
        }
    }

    function kontrol(Request $request)
    {
        if (Auth::check()) {
            echo 'giriş yapıldı';
        } else {
            echo 'giriş yapılmadı';
        }
    }

    function logOut()
    {
        Auth::logOut();
    }

    function ayar(Request $request)
    {
        $tab          = $request->input('tab');
        $mesaj_id     = $request->input('mesaj_id');
        $msg          = $request->session()->get('msg');
        $destek       = new AcrDesController();
        $destek_model = new Destek_model();
        return view('acr_des_v::destek_ayar', compact('destek', 'tab', 'destek_model', 'mesaj_id', 'msg'));
    }

    function index(Request $request)
    {
        $tab = $request->input('tab');
        if (empty($tab)) {
            $tab = 'destek_gelen';
        }
        $mesaj_id     = $request->input('mesaj_id');
        $msg          = $request->session()->get('msg');
        $destek       = new AcrDesController();
        $destek_model = new Destek_model();
        return view('acr_des_v::anasayfa', compact('destek', 'tab', 'destek_model', 'mesaj_id', 'msg'));
    }

    function yeni_mesaj(Request $request)
    {
        $tab          = '';
        $mesaj_id     = $request->input('mesaj_id');
        $msg          = $request->session()->get('msg');
        $destek       = new AcrDesController();
        $destek_model = new Destek_model();
        return view('acr_des_v::yeni_mesaj', compact('destek', 'tab', 'destek_model', 'mesaj_id', 'msg'));
    }

    function mesaj_oku(Request $request)
    {
        $tab = $request->input('tab');
        if (empty($tab)) {
            $tab = 'destek_gelen';
        }
        $mesaj_id     = $request->input('mesaj_id');
        $msg          = $request->session()->get('msg');
        $destek       = new AcrDesController();
        $destek_model = new Destek_model();
        return view('acr_des_v::mesaj_oku', compact('destek', 'tab', 'destek_model', 'mesaj_id', 'msg'));
    }

    function anasayfa(Request $request)
    {
        $tab = $request->input('tab');
        if (empty($tab)) {
            $tab = 'destek_gelen';
        }
        $mesaj_id     = $request->input('mesaj_id');
        $msg          = $request->session()->get('msg');
        $destek       = new AcrDesController();
        $destek_model = new Destek_model();
        return view('acr_des_v::anasayfa', compact('destek', 'tab', 'destek_model', 'mesaj_id', 'msg'));
    }

    function sil(Request $request)
    {
        $destek_id    = $request->input('destek_id');
        $tab          = $request->input('tab');
        $destek_model = new Destek_model();
        if ($tab == 'destek_cop') {
            $destek_model->sil($destek_id);
        } else {
            $destek_model->cope_tasi($destek_id);
        }
        return $destek_id;
    }

    function sil_link(Request $request)
    {
        self::tek_sil($request);
        return redirect()->to('/acr/des?tab=' . $request->input('tab'))->with('msg', $this->silindi);
    }

    function tek_sil(Request $request)
    {
        $destek_id    = $request->input('destek_id');
        $tab          = $request->input('tab');
        $destek_model = new Destek_model();
        if ($tab == 'destek_cop') {
            $destek_model->tek_sil($destek_id);
        } else {
            $destek_model->tek_cope_tasi($destek_id);
        }
        return $destek_id;
    }

    function menu($tab)
    {
        $destek_model = new Destek_model();
        $tab_menu     = $destek_model->tab_menu();
        $link         = '';
        foreach ($tab_menu as $datum => $tab_menus) {
            $okunmayan = $destek_model->gelen_okunmayan_sayi($tab_menus[2]) == 0 ? '' : '<span style="color: red;">' . $destek_model->gelen_okunmayan_sayi($tab_menus[2]) . '</span>';
            $active    = $datum == $tab ? 'class="active"' : '';
            $link      .= '<li ' . $active . ' ><a href="/acr/des?tab=' . $datum . '"><i class="fa fa-' . $tab_menus[1] . '"></i> ' . $tab_menus[0] . ' ' . $okunmayan . ' </a></li>';
        }
        if ($tab == 'destek_ayar') {
            $activeAyar = 'class="active"';
        } else {
            $activeAyar = '';
        }
        if ($destek_model->uye_id() == 1) {
            $admin_ayar = '<li ' . $activeAyar . '><a href="/acr/des/ayar?tab=destek_ayar"><i class="fa  fa-gears"></i>  Admin Ayarlar</a></li>';
        } else {
            $admin_ayar = '';
        }
        return '<div class="col-md-3">
            <a href="/acr/des/yeni_mesaj" class="btn btn-primary btn-block margin-bottom">Yeni Mesaj Gönder</a>
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">DESTEK</h3>
                    <div class="box-tools">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body no-padding">
                    <ul class="nav nav-pills nav-stacked">
                    ' . $link . $admin_ayar . '
                    </ul>
                </div>
                <!-- /.box-body -->
            </div>
        </div>';
    }

    function destek_satir($item, $tab)
    {
        $okunduStyle = $item->okundu == 1 ? 'style="color:#B0C4DE"' : '';
        $konu        = $item->okundu == 1 ? $item->konu : '<b>' . $item->konu . '</b>';
        $item->name  = empty($item->name) ? $item->ad : $item->name;
        $item->name  = empty($item->name) ? 'İsimsiz Üye' : $item->name;
        $veri        =
            '<tr id="destek_satir_' . $item->destek_users_id . '">
                   <td><input id="destek_id[]" name="destek_id[]" value="' . $item->destek_users_id . '"  type="checkbox"></td>
                   <td class="mailbox-name"><a ' . $okunduStyle . ' href="/acr/des/mesaj_oku?mesaj_id=' . $item->destek_users_id . '&tab=' . $tab . '">' . $item->name . '</a></td>
                   <td class="mailbox-subject">' . $konu . '</td>
                   <td class="mailbox-attachment"></td>
                   <td align="right" class="mailbox-date">' . date('d/m/Y H:i', strtotime($item->d_cd)) . '</td>
             </tr>';
        return $veri;
    }

    function mesajlar($tab, $sil)
    {
        $destek_model = new Destek_model();
        $destek_model = $destek_model->tab_menu();
        $tur          = $destek_model[$tab][2];
        $mesajlar     = $destek_model->mesajlar($tur, $sil);
        return $mesajlar;
    }

    function ingilizceYap($metin)
    {
        $search  = array(' ', 'Ç', 'ç', 'Ğ', 'ğ', 'ı', 'İ', 'Ö', 'ö', 'Ş', 'ş', 'Ü', 'ü', '&Ccedil;', '&#286;', '&#304;', '&Ouml;', '&#350;', '&Uuml;', '&ccedil;', '&#287;', '&#305;', '&ouml;', '&#351;', '&uuml;');
        $replace = array('-', 'C', 'c', 'G', 'g', 'i', 'I', 'O', 'o', 'S', 's', 'U', 'u', 'C', 'G', 'I', 'O', 'S', 'U', 'c', 'g', 'i', 'o', 's', 'u');
        $metin   = str_replace($search, $replace, $metin);
        return $metin;
    }

    function destek_mesaj_kaydet(Request $request)
    {
        $mail         = new MailController();
        $destek_model = new Destek_model();
        $mesaj        = $request->input('mesaj');
        $konu         = $request->input('konu');
        $dosya        = $request->file('attachment');
        $uye_id       = $request->input('uye_id');

        $gon_id = $destek_model->uye_id();;
        $ayar     = $destek_model->destek_ayar();
        $email    = $ayar->user_email_stun;
        $mesaj_id = $destek_model->destek_mesaj_kaydet($konu, $mesaj, $uye_id, $gon_id);

        $alan      = $destek_model->alan($uye_id);
        $alan_isim = empty($alan->name) ? $alan->ad : $alan->name;
        if (!empty($dosya)) {
            $size       = round($dosya->getClientSize() / 1000000, 2);
            $type       = strtolower($dosya->getClientOriginalExtension());
            $isim       = str_replace('.' . $type, '', $dosya->getClientOriginalName());
            $dosya_isim = self::ingilizceYap($isim) . '.' . $type;
            $dosya->move(public_path('/uploads'), $dosya_isim);
            if ($size < 21 && $size > 0) {
                $destek_model->destek_dosya_kaydet($mesaj_id, $dosya_isim, $uye_id, $gon_id, $size, $type, $isim);
            } else {
                return redirect()->to('/acr/des/yeni_mesaj')->with('msg', $this->dosyaBuyuk);
            }
        }
        if (!empty($alan->tel) && $ayar->sms_aktiflik == 1) {
            $tel[] = $alan->tel;
            self::smsGonder($_SERVER['SERVER_NAME'] . ' size mesaj gönderdi, sisteme giriş yaparak inceleyebilirsiniz.', $tel, $ayar->sms_user, $ayar->sms_sifre, $ayar->sms_baslik);
        }
        $mail->mailGonder('mail.destek', $alan->$email, $alan_isim, $konu . '<br>', $mesaj);
        return redirect()->to('/acr/des/yeni_mesaj')->with('msg', $this->gonderildi);
    }

    function dosya_indir(Request $request)
    {
        $destek_model       = new Destek_model();
        $destek_dosya_model = new Destek_dosya_model();
        $destek_dosya_id    = $request->input('dosya_id');
        $dosyaSorgu         = $destek_dosya_model->where('id', $destek_dosya_id);
        $dosya_sayi         = $dosyaSorgu->count();
        if ($dosya_sayi > 0) {
            $dosya   = $dosyaSorgu->first();
            $izinler = [
                $dosya->uye_id, $dosya->gon_id
            ];
            if (in_array($destek_model->uye_id(), $izinler)) {
                return response()->download(public_path('/uploads/' . $dosya->dosya_isim), $dosya->dosya_org_isim . '.' . $dosya->type);
            } else {
                return 'Dosya erişiminize izniniz bulunmuyor.';
            }

        } else {
            return 'Dosya mevcut değil.';
        }
    }

    function ayar_kaydet(Request $request)
    {
        $destek_model = new Destek_model();
        $veri         = [
            'destek_mail'       => $request->input('destek_mail'),
            'sms_user'          => $request->input('sms_user'),
            'sms_sifre'         => $request->input('sms_sifre'),
            'destek_admin_isim' => $request->input('destek_admin_isim'),
            'sms_aktiflik'      => $request->input('sms_aktiflik'),
            'sms_baslik'        => $request->input('sms_baslik'),
            'user_name_stun'    => $request->input('user_name_stun'),
            'user_id_stun'      => $request->input('user_id_stun'),
            'user_email_stun'   => $request->input('user_email_stun'),
        ];
        $destek_model->destek_ayar_kaydet($veri);
        return redirect()->back()->with('msg', $this->basariliGuncelleme);
    }

    function smsGonder($mesaj, $tel, $user, $password, $baslik)
    {
        $mesaj   = $mesaj;
        $telDizi = $tel;
        array_unique($telDizi);
        $mesajData['user']      = array(
            'name' => $user,
            'pass' => $password
        );
        $mesajData['msgBaslik'] = $baslik;
        $mesajData['msgData'][] = array(
            'tel' => $telDizi,
            'msg' => $mesaj,
        );
        self::MesajPaneliGonder($mesajData);
    }

    function MesajPaneliGonder($request)
    {
        $request = "data=" . base64_encode(json_encode($request));
        $ch      = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://api.mesajpaneli.com/json_api/');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode(base64_decode($result), TRUE);
    }
}