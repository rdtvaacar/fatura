@extends('acr_destek.index')
@section('acr_destek')
    <section class="content">
        <?php echo empty($msg) ? '' : $msg; ?>
        <div class="row">
        <?php

        echo $destek->menu($tab);
        if ($mesaj_id) {
            $mesaj        = $destek_model->mesaj_oku($mesaj_id);
            $konu         = 'RE: ' . $mesaj->konu;
            $mesaj_icerik = '<br><br>.............................................................. <br>Cevabı üst satıra yazınız<br>' . $mesaj->du_cd . '<br>' . $mesaj->mesaj;
            $uye_id       = $mesaj->gon_id;
            $name         = empty($mesaj->name) ? $mesaj->ad : $mesaj->name;
        } else {
            $konu         = '';
            $mesaj_icerik = '';
            $uye_id       = 1;
            $name         = 'Admin';
        }
        ?>
        <!-- /.col -->
            <form action="/acr/des/destek_mesaj_kaydet" method="post" enctype="multipart/form-data">
                <?php echo csrf_field() ?>
                <div class="col-md-9">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Yeni Mesaj Oluşturun</h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="form-group">
                                <input class="form-control" disabled placeholder="Kime:" value="<?php echo $name ?>">
                                <input id="uye_id" name="uye_id" value="<?php echo $uye_id ?>" type="hidden"/>
                            </div>
                            <div class="form-group">
                                <input name="konu" id="konu" class="form-control" placeholder="Konu:" value="<?php echo $konu?>">
                            </div>
                            <div class="form-group">
                                <textarea name="mesaj" id="compose-textarea" class="form-control" style="height: 300px"><?php echo $mesaj_icerik ?></textarea>
                            </div>
                            <div class="form-group">
                                <div class="btn btn-default btn-file">
                                    <i class="fa fa-paperclip"></i> Dosya
                                    <input type="file" name="attachment">
                                </div>
                                <p class="help-block">En Fazla. 20MB</p>
                            </div>
                        </div>
                        <!-- /.box-body -->
                        <div class="box-footer">
                            <div class="pull-right">
                                <button type="submit" id="myButton" data-loading-text="Loading..." id="gonder" class="btn btn-primary"><i class="fa fa-envelope-o"></i> Gönder</button>
                            </div>
                            <a href="/acr/des/" type="reset" class="btn btn-default"><i class="fa fa-times"></i> Vazgeç</a>
                        </div>
                        <!-- /.box-footer -->
                    </div>
                    <!-- /. box -->
                </div>
            </form>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </section>
@stop
