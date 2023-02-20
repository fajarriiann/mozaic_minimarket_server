@inject('PurchasePayment', 'App\Http\Controllers\PurchasePaymentController')
@extends('adminlte::page')

@section('title', 'MOZAIC Minimarket')

@section('js')
<script>

    function elements_add(name, value){
        $.ajax({
            type: "POST",
            url : "{{route('elements-add-purchase-payment')}}",
            dataType: "html",
            data: {
                'name'      : name,
                'value'	    : value,
                '_token'    : '{{csrf_token()}}',
            },
            success: function(return_data){ 
            },
        });
    }

</script>
@stop
@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ url('purchase-payment') }}">Daftar Pelunasan Hutang</a></li>
        <li class="breadcrumb-item"><a href="{{ url('purchase-payment/search') }}">Daftar Supplier</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tambah Pelunasan Hutang</li>
    </ol>
  </nav>

@stop

@section('content')

<h3 class="page-title">
    Form Tambah Pelunasan Hutang
</h3>
<br/>
@if(session('msg'))
<div class="alert alert-info" role="alert">
    {{session('msg')}}
</div>
@endif
<div class="card border border-dark">
    <div class="card-header border-dark bg-dark">
        <h5 class="mb-0 float-left">
            Form Tambah
        </h5>
        <div class="float-right">
            <button onclick="location.href='{{ url('purchase-payment') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>
    <form method="post" action="{{ route('process-add-purchase-payment') }}" enctype="multipart/form-data">
    @csrf
    <div class="card-body">
        <div class="row form-group">
            <div class="col-md-4">
                <div class="form-group">
                    <section class="control-label">Tanggal Pelunasan
                        <span class="required text-danger">
                            *
                        </span>
                    </section>
                    <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="payment_date" id="payment_date" onChange="elements_add(this.name, this.value);" value="{{ $purchasepaymentelements['payment_date'] == '' ? date('Y-m-d') : $purchasepaymentelements['payment_date'] }}" style="width: 50%;"/>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <a class="text-dark">Nama Supplier</a>
                    <input class="form-control input-bb" type="text" name="supplier_name" id="supplier_name" value="{{ $supplier['supplier_name'] }}" readonly/>
                    <input class="form-control input-bb" type="hidden" name="supplier_id" id="supplier_id" value="{{ $supplier['supplier_id'] }}" readonly/>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <section class="control-label">Metode Pembayaran
                        <span class="required text-danger">
                            *
                        </span>
                        {!! Form::select('',  $payment_method_list, $purchasepaymentelements['payment_method'], ['class' => 'form-control selection-search-clear select-form','name' => 'payment_method', 'id' => 'payment_method', 'onchange' => 'elements_add(this.name, this.value);']) !!}
                    </section>
                </div>
            </div>
            <div class="col-md-8 ">
                <div class="form-group">
                    <a class="text-dark">Keterangan</a>
                    <textarea rows="3" type="text" class="form-control input-bb" name="payment_remark" onChange="elements_add(this.name, this.value);" id="payment_remark" autocomplete='off'>{{ $purchasepaymentelements['payment_remark'] }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border border-dark">
    <div class="card-header border-dark bg-dark">
        <h5 class="mb-0 float-left">
            Daftar
        </h5>
    </div>
    <div class="card-body">
        <div class="form-body form">
            <div class="table-responsive">
                <table class="table table-bordered table-advance table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th style='text-align:center'>No</th>
                            <th style='text-align:center'>Tanggal Pembelian</th>
                            <th style='text-align:center'>Tanggal Jatuh Tempo</th>
                            <th style='text-align:center'>No. Pembelian</th>
                            <th style='text-align:center'>Jumlah Retur</th>
                            <th style='text-align:center'>Jumlah Hutang</th>
                            <th style='text-align:center'>Aksi</th>
                        </tr>
                    </thead>
                    @php
                        $no = 1;
                    @endphp
                        @foreach ($purchaseinvoice as $key => $val)
                            <tr>
                                <td class="text-center">{{ $no++ }}.</td>
                                <td>{{ date('d-m-Y', strtotime($val['purchase_invoice_date'])) }}</td>
                                <td>{{ date('d-m-Y', strtotime($val['purchase_invoice_due_date'])) }}</td>
                                <td>{{ $val['purchase_invoice_no'] }}</td>
                                <td class="text-right">{{ number_format($val['return_amount'],2,'.',',') }}</td>
                                <td class="text-right">{{ number_format($val['total_amount'] - $val['return_amount'],2,'.',',') }}</td>
                                <td class="text-center">
                                    <input class="checkbox-lg text-center" type="checkbox" id="purchase_invoice_id_{{ $no }}" name="purchase_invoice_id_{{ $no }}" value="{{ $val['purchase_invoice_id'] }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <input type="text" id="total_invoice" name="total_invoice" value="{{ $no }}" hidden>
                </table>
            </div>
        </div>
    </div>
    <div class="card-footer text-muted">
        <div class="form-actions float-right">
            <button type="reset" name="Reset" class="btn btn-danger" onClick="window.location.reload();"><i class="fa fa-times"></i> Batal</button>
            <button type="submit" name="Save" class="btn btn-success" title="Save"><i class="fa fa-check"></i> Simpan</button>
        </div>
    </div>
</div>
</div>
</form>

@stop

@section('footer')
    
@stop

@section('css')
@stop