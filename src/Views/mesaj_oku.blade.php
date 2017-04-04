@extends('acr_fatura.index')
@section('acr_fatura')
    <section class="content">
        <div class="row">
        <?php echo $fatura->menu($tab);
        $mesaj = $fatura_model->mesaj_oku($mesaj_id);

        ?>
        <!-- /.col -->
            <div class="col-md-9">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Mesaj Oku</h3>

                        <div class="box-tools pull-right">
                            <a href="#" class="btn btn-box-tool" data-toggle="tooltip" title="Previous"><i class="fa fa-chevron-left"></i></a>
                            <a href="#" class="btn btn-box-tool" data-toggle="tooltip" title="Next"><i class="fa fa-chevron-right"></i></a>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body no-padding">
                        <div class="mailbox-read-info">
                            <h3><span style="color: #1b6cbe"><?php echo $mesaj->name = empty($mesaj->name) ? $mesaj->ad : $mesaj->name ?></span> - <?php echo $mesaj->konu ?></h3>
                            <h5><span class="mailbox-read-time pull-right"><?php echo date('d/m/Y H:i', strtotime($mesaj->du_cd)) ?></span></h5>
                        </div>
                        <!-- /.mailbox-read-info -->
                        <div class="mailbox-controls with-border text-center">
                            <div class="btn-group">
                                <a href="/acr/fat/sil_link?fatura_id=<?php echo $mesaj_id ?>&tab=<?php echo $tab ?>" type="button" class="btn btn-default btn-sm" data-toggle="tooltip" data-container="body"
                                   title="Delete">
                                    <i class="fa fa-trash-o"></i></a>
                                <a href="/acr/fat/yeni_mesaj?mesaj_id=<?php echo $mesaj_id ?>" class="btn btn-default btn-sm" data-toggle="tooltip" data-container="body" title="Cevapla">
                                    <i class="fa fa-reply"></i></a>

                            </div>
                            <!-- /.btn-group -->

                        </div>
                        <!-- /.mailbox-controls -->
                        <div class="mailbox-read-message">
                            <?php echo $mesaj->mesaj ?>
                        </div>
                        <!-- /.mailbox-read-message -->
                    </div>
                    <!-- /.box-body -->
                    <?php if(!empty($mesaj->dosya_isim)) { ?>
                    <div class="box-footer">

                        <ul class="mailbox-attachments clearfix">
                            <li>
                                <div class="mailbox-attachment-info">
                                    <a href="/acr/fat/dosya_indir?dosya_id=<?php echo $mesaj->fatura_dosya_id ?>" class="mailbox-attachment-name"><i class="fa fa-paperclip"></i> <?php echo $mesaj->dosya_org_isim ?></a>
                                    <span class="mailbox-attachment-size">
                                    <?php echo $mesaj->size ?> MB
                          <a href="/acr/fat/dosya_indir?dosya_id=<?php echo $mesaj->fatura_dosya_id ?>" class="btn btn-default btn-xs pull-right"><i
                                      class="fa fa-cloud-download"></i></a>
                        </span>
                                </div>
                            </li>

                        </ul>
                    </div>
                <?php } ?>
                <!-- /.box-footer -->
                    <div class="box-footer">
                        <div class="pull-right">
                            <a href="/acr/fat/yeni_mesaj?mesaj_id=<?php echo $mesaj_id ?>" class="btn btn-default"><i class="fa fa-reply"></i> Cevapla</a>
                        </div>
                        <a href="/acr/fat/fatura_sil?fatura_id=<?php echo $mesaj_id ?>&tab=<?php echo $tab ?>" class="btn btn-default"><i class="fa fa-trash-o"></i> Sil</a>
                    </div>
                    <!-- /.box-footer -->
                </div>
                <!-- /. box -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </section>
@stop