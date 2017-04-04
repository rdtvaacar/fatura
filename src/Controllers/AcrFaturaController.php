<?php

namespace Acr\Fat\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Acr\Fat\Model\Fatura_model;
use Acr\Fat\Model\Fatura_dosya_model;
use Auth;
use Acr\Fat\Controllers\MailController;

class AcrFatController extends Controller
{
    protected $basarili           = '<div class="alert alert-success">Başarıyla Eklendi</div>';
    protected $silindi            = '<div class="alert alert-warning">Başarıyla Silindi</div>';
    protected $dosyaBuyuk         = '<div class="alert alert-danger">Yüklemeye çalıştığınız dosyanın boyutu 20 MB\'den büyük</div>';
    protected $gonderildi         = '<div class="alert alert-success">Mesajınız başarıyla gönderildi, en kısa zamanda size yanıt vermeye çalışacağız, teşekkür ederiz.</div>';
    protected $basariliGuncelleme = '<div class="alert alert-success">Başarıyla Güncellendi</div>';

    function login(Request $request)
    {
        $fatura_model = new Fatura_model();
        if ($request->server('SERVER_NAME') == 'fatura2') {
            Auth::loginUsingId(1, true);
            echo $fatura_model->uye_id();
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
        $fatura       = new AcrFatController();
        $fatura_model = new Fatura_model();
        return view('acr_fat_v::fatura_ayar', compact('fatura', 'tab', 'fatura_model', 'mesaj_id', 'msg'));
    }

    function index(Request $request)
    {
        $tab = $request->input('tab');
        if (empty($tab)) {
            $tab = 'fatura_gelen';
        }
        $mesaj_id     = $request->input('mesaj_id');
        $msg          = $request->session()->get('msg');
        $fatura       = new AcrFatController();
        $fatura_model = new Fatura_model();
        return view('acr_fat_v::anasayfa', compact('fatura', 'tab', 'fatura_model', 'mesaj_id', 'msg'));
    }

    function yeni_mesaj(Request $request)
    {
        $tab          = '';
        $mesaj_id     = $request->input('mesaj_id');
        $msg          = $request->session()->get('msg');
        $fatura       = new AcrFatController();
        $fatura_model = new Fatura_model();
        return view('acr_fat_v::yeni_mesaj', compact('fatura', 'tab', 'fatura_model', 'mesaj_id', 'msg'));
    }

    function mesaj_oku(Request $request)
    {
        $tab = $request->input('tab');
        if (empty($tab)) {
            $tab = 'fatura_gelen';
        }
        $mesaj_id     = $request->input('mesaj_id');
        $msg          = $request->session()->get('msg');
        $fatura       = new AcrFatController();
        $fatura_model = new Fatura_model();
        return view('acr_fat_v::mesaj_oku', compact('fatura', 'tab', 'fatura_model', 'mesaj_id', 'msg'));
    }

    function anasayfa(Request $request)
    {
        $tab = $request->input('tab');
        if (empty($tab)) {
            $tab = 'fatura_gelen';
        }
        $mesaj_id     = $request->input('mesaj_id');
        $msg          = $request->session()->get('msg');
        $fatura       = new AcrFatController();
        $fatura_model = new Fatura_model();
        return view('acr_fat_v::anasayfa', compact('fatura', 'tab', 'fatura_model', 'mesaj_id', 'msg'));
    }

    function sil(Request $request)
    {
        $fatura_id    = $request->input('fatura_id');
        $tab          = $request->input('tab');
        $fatura_model = new Fatura_model();
        if ($tab == 'fatura_cop') {
            $fatura_model->sil($fatura_id);
        } else {
            $fatura_model->cope_tasi($fatura_id);
        }
        return $fatura_id;
    }

    function sil_link(Request $request)
    {
        self::tek_sil($request);
        return redirect()->to('/acr/fat?tab=' . $request->input('tab'))->with('msg', $this->silindi);
    }

    function tek_sil(Request $request)
    {
        $fatura_id    = $request->input('fatura_id');
        $tab          = $request->input('tab');
        $fatura_model = new Fatura_model();
        if ($tab == 'fatura_cop') {
            $fatura_model->tek_sil($fatura_id);
        } else {
            $fatura_model->tek_cope_tasi($fatura_id);
        }
        return $fatura_id;
    }

    function menu($tab)
    {
        $fatura_model = new Fatura_model();
        $tab_menu     = $fatura_model->tab_menu();
        $link         = '';
        foreach ($tab_menu as $datum => $tab_menus) {
            $okunmayan = $fatura_model->gelen_okunmayan_sayi($tab_menus[2]) == 0 ? '' : '<span style="color: red;">' . $fatura_model->gelen_okunmayan_sayi($tab_menus[2]) . '</span>';
            $active    = $datum == $tab ? 'class="active"' : '';
            $link      .= '<li ' . $active . ' ><a href="/acr/fat?tab=' . $datum . '"><i class="fa fa-' . $tab_menus[1] . '"></i> ' . $tab_menus[0] . ' ' . $okunmayan . ' </a></li>';
        }
        if ($tab == 'fatura_ayar') {
            $activeAyar = 'class="active"';
        } else {
            $activeAyar = '';
        }
        if ($fatura_model->uye_id() == 1) {
            $admin_ayar = '<li ' . $activeAyar . '><a href="/acr/fat/ayar?tab=fatura_ayar"><i class="fa  fa-gears"></i>  Admin Ayarlar</a></li>';
        } else {
            $admin_ayar = '';
        }
        return '<div class="col-md-3">
            <a href="/acr/fat/yeni_mesaj" class="btn btn-primary btn-block margin-bottom">Yeni Mesaj Gönder</a>
            <div class="box box-solid">
                <div class="box-header with-border">
                    <h3 class="box-title">Fatura</h3>
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

    function fatura_satir($item, $tab)
    {
        $okunduStyle = $item->okundu == 1 ? 'style="color:#B0C4DE"' : '';
        $konu        = $item->okundu == 1 ? $item->konu : '<b>' . $item->konu . '</b>';
        $item->name  = empty($item->name) ? $item->ad : $item->name;
        $item->name  = empty($item->name) ? 'İsimsiz Üye' : $item->name;
        $veri        =
            '<tr id="fatura_satir_' . $item->fatura_users_id . '">
                   <td><input id="fatura_id[]" name="fatura_id[]" value="' . $item->fatura_users_id . '"  type="checkbox"></td>
                   <td class="mailbox-name"><a ' . $okunduStyle . ' href="/acr/fat/mesaj_oku?mesaj_id=' . $item->fatura_users_id . '&tab=' . $tab . '">' . $item->name . '</a></td>
                   <td class="mailbox-subject">' . $konu . '</td>
                   <td class="mailbox-attachment"></td>
                   <td align="right" class="mailbox-date">' . date('d/m/Y H:i', strtotime($item->d_cd)) . '</td>
             </tr>';
        return $veri;
    }

    function mesajlar($tab, $sil)
    {
        $fatura_model = new Fatura_model();
        $fatura_model = $fatura_model->tab_menu();
        $tur          = $fatura_model[$tab][2];
        $mesajlar     = $fatura_model->mesajlar($tur, $sil);
        return $mesajlar;
    }

    function ingilizceYap($metin)
    {
        $search  = array(' ', 'Ç', 'ç', 'Ğ', 'ğ', 'ı', 'İ', 'Ö', 'ö', 'Ş', 'ş', 'Ü', 'ü', '&Ccedil;', '&#286;', '&#304;', '&Ouml;', '&#350;', '&Uuml;', '&ccedil;', '&#287;', '&#305;', '&ouml;', '&#351;', '&uuml;');
        $replace = array('-', 'C', 'c', 'G', 'g', 'i', 'I', 'O', 'o', 'S', 's', 'U', 'u', 'C', 'G', 'I', 'O', 'S', 'U', 'c', 'g', 'i', 'o', 's', 'u');
        $metin   = str_replace($search, $replace, $metin);
        return $metin;
    }

    function fatura_mesaj_kaydet(Request $request)
    {
        $mail         = new MailController();
        $fatura_model = new Fatura_model();
        $mesaj        = $request->input('mesaj');
        $konu         = $request->input('konu');
        $dosya        = $request->file('attachment');
        $uye_id       = $request->input('uye_id');

        $gon_id = $fatura_model->uye_id();;
        $ayar     = $fatura_model->fatura_ayar();
        $email    = $ayar->user_email_stun;
        $mesaj_id = $fatura_model->fatura_mesaj_kaydet($konu, $mesaj, $uye_id, $gon_id);

        $alan      = $fatura_model->alan($uye_id);
        $alan_isim = empty($alan->name) ? $alan->ad : $alan->name;
        if (!empty($dosya)) {
            $size       = round($dosya->getClientSize() / 1000000, 2);
            $type       = strtolower($dosya->getClientOriginalExtension());
            $isim       = str_replace('.' . $type, '', $dosya->getClientOriginalName());
            $dosya_isim = self::ingilizceYap($isim) . '.' . $type;
            $dosya->move(public_path('/uploads'), $dosya_isim);
            if ($size < 21 && $size > 0) {
                $fatura_model->fatura_dosya_kaydet($mesaj_id, $dosya_isim, $uye_id, $gon_id, $size, $type, $isim);
            } else {
                return redirect()->to('/acr/fat/yeni_mesaj')->with('msg', $this->dosyaBuyuk);
            }
        }
        if (!empty($alan->tel) && $ayar->sms_aktiflik == 1) {
            $tel[] = $alan->tel;
            self::smsGonder($_SERVER['SERVER_NAME'] . ' size mesaj gönderdi, sisteme giriş yaparak inceleyebilirsiniz.', $tel, $ayar->sms_user, $ayar->sms_sifre, $ayar->sms_baslik);
        }
        $mail->mailGonder('mail.fatura', $alan->$email, $alan_isim, $konu . '<br>', $mesaj);
        return redirect()->to('/acr/fat/yeni_mesaj')->with('msg', $this->gonderildi);
    }

    function dosya_indir(Request $request)
    {
        $fatura_model       = new Fatura_model();
        $fatura_dosya_model = new Fatura_dosya_model();
        $fatura_dosya_id    = $request->input('dosya_id');
        $dosyaSorgu         = $fatura_dosya_model->where('id', $fatura_dosya_id);
        $dosya_sayi         = $dosyaSorgu->count();
        if ($dosya_sayi > 0) {
            $dosya   = $dosyaSorgu->first();
            $izinler = [
                $dosya->uye_id, $dosya->gon_id
            ];
            if (in_array($fatura_model->uye_id(), $izinler)) {
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
        $fatura_model = new Fatura_model();
        $veri         = [
            'fatura_mail'       => $request->input('fatura_mail'),
            'sms_user'          => $request->input('sms_user'),
            'sms_sifre'         => $request->input('sms_sifre'),
            'fatura_admin_isim' => $request->input('fatura_admin_isim'),
            'sms_aktiflik'      => $request->input('sms_aktiflik'),
            'sms_baslik'        => $request->input('sms_baslik'),
            'user_name_stun'    => $request->input('user_name_stun'),
            'user_id_stun'      => $request->input('user_id_stun'),
            'user_email_stun'   => $request->input('user_email_stun'),
        ];
        $fatura_model->fatura_ayar_kaydet($veri);
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