<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AcctAccount;
use App\Models\Expenditure;
use App\Models\JournalVoucher;
use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class AcctProfitLossReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        
    }

    public function index()
    {
        if(!$start_date = Session::get('start_date')){
            $start_date = date('Y-m-d');
        } else {
            $start_date = Session::get('start_date');
        }
        if(!$end_date = Session::get('end_date')){
            $end_date = date('Y-m-d');
        } else {
            $end_date = Session::get('end_date');
        }
        // $sales_invoice = SalesInvoice::join('sales_invoice_item','sales_invoice_item.sales_invoice_id','=','sales_invoice.sales_invoice_id')
        // ->where('sales_invoice.sales_invoice_date','>=',$start_date)
        // ->where('sales_invoice.sales_invoice_date','<=',$end_date)
        // ->where('sales_invoice.data_state',0)
        // ->where('sales_invoice.company_id', Auth::user()->company_id)
        // ->get();
        // $total_sales_amount = 0;
        // foreach($sales_invoice as $row){
        //     $total_sales_amount += $row['total_amount'];
        // }

        // $purchase_invoice = PurchaseInvoice::join('purchase_invoice_item','purchase_invoice_item.purchase_invoice_id','=','purchase_invoice.purchase_invoice_id')
        // ->where('purchase_invoice.purchase_invoice_date','>=',$start_date)
        // ->where('purchase_invoice.purchase_invoice_date','<=',$end_date)
        // ->where('purchase_invoice.data_state',0)
        // ->where('purchase_invoice.company_id', Auth::user()->company_id)
        // ->get();
        // $total_purchase_amount = 0;
        // foreach($purchase_invoice as $row){
        //     $total_purchase_amount += $row['total_amount'];
        // }

        // $expenditure = Expenditure::where('company_id', Auth::user()->company_id)
        // ->where('data_state',0)
        // ->where('expenditure_date','>=',$start_date)
        // ->where('expenditure_date','<=',$end_date)
        // ->get();
        // $total_expenditure_amount = 0;
        // foreach($expenditure as $row){
        //     $total_expenditure_amount += $row['expenditure_amount'];
        // }

        $income = AcctAccount::where('data_state',0)
        ->where('account_type_id',2)
        ->where('company_id', Auth::user()->company_id)
        ->get();

        $expenditure = AcctAccount::where('data_state',0)
        ->where('account_type_id',3)
        ->where('company_id', Auth::user()->company_id)
        ->get();

        return view('content.AcctProfitLossReport.ListAcctProfitLossReport',compact('start_date','end_date','income','expenditure'));
    }

    public function filterProfitLossReport(Request $request)
    {
        $start_date = $request->start_date;
        $end_date   = $request->end_date;

        Session::put('start_date', $start_date);
        Session::put('end_date', $end_date);

        return redirect('/profit-loss-report');
    }

    public function resetFilterProfitLossReport()
    {
        Session::forget('start_date');
        Session::forget('end_date');

        return redirect('/profit-loss-report');
    }

    public function getAmountAccount($account_id)
    {
        if(!$start_date = Session::get('start_date')){
            $start_date = date('Y-m-d');
        } else {
            $start_date = Session::get('start_date');
        }
        if(!$end_date = Session::get('end_date')){
            $end_date = date('Y-m-d');
        } else {
            $end_date = Session::get('end_date');
        }

        $data = JournalVoucher::join('acct_journal_voucher_item','acct_journal_voucher_item.journal_voucher_id','acct_journal_voucher.journal_voucher_id')
        ->where('acct_journal_voucher.journal_voucher_date', '>=', $start_date)
        ->where('acct_journal_voucher.journal_voucher_date', '<=', $end_date)
        ->where('acct_journal_voucher.data_state',0)
        ->where('acct_journal_voucher_item.account_id', $account_id)
        ->where('acct_journal_voucher.company_id', Auth::user()->company_id)
        ->get();
        $data_first = JournalVoucher::join('acct_journal_voucher_item','acct_journal_voucher_item.journal_voucher_id','acct_journal_voucher.journal_voucher_id')
        ->where('acct_journal_voucher.journal_voucher_date', '>=', $start_date)
        ->where('acct_journal_voucher.journal_voucher_date', '<=', $end_date)
        ->where('acct_journal_voucher.data_state',0)
        ->where('acct_journal_voucher.company_id', Auth::user()->company_id)
        ->where('acct_journal_voucher_item.account_id', $account_id)
        ->first();
        
        $amount = 0;
        $amount1 = 0;
        $amount2 = 0;
        foreach ($data as $key => $val) {

            if ($val['account_id_status'] == $data_first['account_id_status']) {
                $amount1 += $val['journal_voucher_amount'];
            } else {
                $amount2 += $val['journal_voucher_amount'];

            }
            $amount = $amount1 - $amount2;
        }
        
        return $amount;
    }

    public function printProfitLossReport()
    {
        if(!$start_date = Session::get('start_date')){
            $start_date = date('Y-m-d');
        } else {
            $start_date = Session::get('start_date');
        }
        if(!$end_date = Session::get('end_date')){
            $end_date = date('Y-m-d');
        } else {
            $end_date = Session::get('end_date');
        }

        $income = AcctAccount::where('data_state',0)
        ->where('account_type_id',2)
        ->where('company_id', Auth::user()->company_id)
        ->get();

        $expenditure = AcctAccount::where('data_state',0)
        ->where('account_type_id',3)
        ->where('company_id', Auth::user()->company_id)
        ->get();

        $pdf = new TCPDF('P', PDF_UNIT, 'F4', true, 'UTF-8', false);

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);

        $pdf::SetMargins(40, 10, 40, 10); // put space of 10 on top

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $pdf::SetFont('helvetica', 'B', 20);

        $pdf::AddPage();

        $pdf::SetFont('helvetica', '', 10);

        $tbl = "
        <table cellspacing=\"0\" cellpadding=\"2\" border=\"0\">
            <tr>
                <td><div style=\"text-align: center; font-size:14px; font-weight: bold\">LAPORAN RUGI LABA</div></td>
            </tr>
            <tr>
                <td><div style=\"text-align: center; font-size:12px\">PERIODE : ".date('d M Y', strtotime($start_date))." s.d. ".date('d M Y', strtotime($end_date))."</div></td>
            </tr>
            <br>
            <br>
        </table>
        ";
        $pdf::writeHTML($tbl, true, false, false, false, '');
        
        $no = 1;
        
        $tblStock1 = "
        <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
            <tr>
                <th colspan=\"2\" style=\"font-weight: bold\">Pendapatan</th>
            </tr>
        ";
        $tblStock2 = "";
        $amount_income = 0;
        foreach ($income as $key => $val) {
            $tblStock2 .= "
            <tr>
                <td style=\"width: 80%\">   ".$val['account_code'].' - '.$val['account_name']."</td>
                <td style=\"width: 20%; text-align: right\">".number_format($this->getAmountAccount($val['account_id']),2,'.',',')."</td>
            </tr>
            ";
            $amount_income += $this->getAmountAccount($val['account_id']);
        }
        $tblStock3 = "
            <br>
            <tr>
                <th style=\"font-weight: bold; text-align:center\">TOTAL PENDAPATAN</th>
                <th style=\"font-weight: bold; text-align:center\">".number_format($amount_income,2,'.',',')."</th>
            </tr>
            <br>
            <tr>
                <th colspan=\"2\" style=\"font-weight: bold\">Pengeluaran</th>
            </tr>
        ";
        $expenditure1 = 0;
        foreach ($expenditure as $key => $val) {
            $tblStock3 .= "
            <tr>
                <td style=\"width: 80%\">   ".$val['account_code'].' - '.$val['account_name']."</td>
                <td style=\"width: 20%; text-align: right\">".number_format($this->getAmountAccount($val['account_id']),2,'.',',')."</td>
            </tr>
            ";
            $expenditure1 += $this->getAmountAccount($val['account_id']);
        }

        $tblStock5 = "
            <br>
            <br>
            <tr>
                <th style=\"font-weight: bold; text-align: center\">TOTAL PENGELUARAN</th>
                <th style=\"font-weight: bold; text-align:center\">".number_format($expenditure1,2,'.',',')."</th>
            </tr>
            <tr>
                <th style=\"font-weight: bold; text-align: center\">RUGI / LABA</th>
                <th style=\"font-weight: bold; text-align:center\">".number_format(($amount_income - $expenditure1),2,'.',',')."</th>
            </tr>
        </table>";

        $pdf::writeHTML($tblStock1.$tblStock2.$tblStock3.$tblStock5, true, false, false, false, '');

        $filename = 'Laporan_Rugi_Laba_'.$start_date.'s.d.'.$end_date.'.pdf';
        $pdf::Output($filename, 'I');
    }

    public function exportProfitLossReport()
    {
        if(!$start_date = Session::get('start_date')){
            $start_date = date('Y-m-d');
        } else {
            $start_date = Session::get('start_date');
        }
        if(!$end_date = Session::get('end_date')){
            $end_date = date('Y-m-d');
        } else {
            $end_date = Session::get('end_date');
        }
        // $sales_invoice = SalesInvoice::join('sales_invoice_item','sales_invoice_item.sales_invoice_id','=','sales_invoice.sales_invoice_id')
        // ->where('sales_invoice.sales_invoice_date','>=',$start_date)
        // ->where('sales_invoice.sales_invoice_date','<=',$end_date)
        // ->where('sales_invoice.data_state',0)
        // ->where('sales_invoice.company_id', Auth::user()->company_id)
        // ->get();
        // $total_sales_amount = 0;
        // foreach($sales_invoice as $row){
        //     $total_sales_amount += $row['total_amount'];
        // }

        // $purchase_invoice = PurchaseInvoice::join('purchase_invoice_item','purchase_invoice_item.purchase_invoice_id','=','purchase_invoice.purchase_invoice_id')
        // ->where('purchase_invoice.purchase_invoice_date','>=',$start_date)
        // ->where('purchase_invoice.purchase_invoice_date','<=',$end_date)
        // ->where('purchase_invoice.data_state',0)
        // ->where('purchase_invoice.company_id', Auth::user()->company_id)
        // ->get();
        // $total_purchase_amount = 0;
        // foreach($purchase_invoice as $row){
        //     $total_purchase_amount += $row['total_amount'];
        // }

        // $expenditure = Expenditure::where('company_id', Auth::user()->company_id)
        // ->where('data_state',0)
        // ->where('expenditure_date','>=',$start_date)
        // ->where('expenditure_date','<=',$end_date)
        // ->get();
        // $total_expenditure_amount = 0;
        // foreach($expenditure as $row){
        //     $total_expenditure_amount += $row['expenditure_amount'];
        // }

        // $subtotal_expenditure = $total_purchase_amount + $total_expenditure_amount;
        // $subtotal_difference = $total_sales_amount - $subtotal_expenditure;
        $income = AcctAccount::where('data_state',0)
        ->where('account_type_id',2)
        ->where('company_id', Auth::user()->company_id)
        ->get();

        $expenditure = AcctAccount::where('data_state',0)
        ->where('account_type_id',3)
        ->where('company_id', Auth::user()->company_id)
        ->get();    

        $spreadsheet = new Spreadsheet();

        // if(!empty($sales_invoice || $purchase_invoice || $expenditure)){
            $spreadsheet->getProperties()->setCreator("MOZAIC")
                                        ->setLastModifiedBy("MOZAIC")
                                        ->setTitle("Profit Loss Report")
                                        ->setSubject("")
                                        ->setDescription("Profit Loss Report")
                                        ->setKeywords("Profit, Loss, Report")
                                        ->setCategory("Profit Loss Report");
                                 
            $sheet = $spreadsheet->getActiveSheet(0);
            $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(40);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(25);
    
            $spreadsheet->getActiveSheet()->mergeCells("B1:C1");
            $spreadsheet->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
            $spreadsheet->getActiveSheet()->mergeCells("B2:C2");
            $spreadsheet->getActiveSheet()->getStyle('B2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->mergeCells("B4:C4");
            $spreadsheet->getActiveSheet()->getStyle('B4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
            $spreadsheet->getActiveSheet()->getStyle('B4')->getFont()->setBold(true);
            $spreadsheet->getActiveSheet()->getStyle('B9')->getFont()->setBold(true);
            $spreadsheet->getActiveSheet()->getStyle('B7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B14')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B15')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('C7')->getFont()->setBold(true);
            $spreadsheet->getActiveSheet()->getStyle('C14')->getFont()->setBold(true);
            $spreadsheet->getActiveSheet()->getStyle('C15')->getFont()->setBold(true);
            $spreadsheet->getActiveSheet()->getStyle('B7')->getFont()->setBold(true);
            $spreadsheet->getActiveSheet()->getStyle('B14')->getFont()->setBold(true);
            $spreadsheet->getActiveSheet()->getStyle('B15')->getFont()->setBold(true);
            for ($i=0; $i < 37 ; $i++) { 
                $spreadsheet->getActiveSheet()->getStyle('C'.$i)->getNumberFormat()->setFormatCode('0.00');
            }


            $sheet->setCellValue('B1',"LAPORAN RUGI LABA");	
            $sheet->setCellValue('B2', 'Period '.date('d M Y', strtotime($start_date))." s.d. ".date('d M Y', strtotime($end_date)));
            $sheet->setCellValue('B4',"PENDAPATAN");
            $no = 5;
            $no1 = 5;
            $income_amount = 0;
            $expenditure1 = 0;
            $expenditure2 = 0;
            foreach ($income as $key => $val) {
                $sheet->setCellValue('B'.$no++, '  '.$val['account_code'].' - '.$val['account_name']);
                $sheet->setCellValue('C'.$no1++, $this->getAmountAccount($val['account_id']));
                $income_amount += $this->getAmountAccount($val['account_id']);
            }
            $no++;
            $sheet->setCellValue('B'.$no++,"TOTAL PENDAPATAN");
            $no1 = 7;
            $sheet->setCellValue('C'.$no1, $income_amount);
            $no++;
            $sheet->setCellValue('B'.$no++,"PENGELUARAN");
            $no1 = 10;
            foreach ($expenditure as $key => $val) {
                $sheet->setCellValue('B'.$no++, '  '.$val['account_code'].' - '.$val['account_name']);
                $sheet->setCellValue('C'.$no1++, $this->getAmountAccount($val['account_id']));
                $expenditure1 += $this->getAmountAccount($val['account_id']);
            }
            $no++;
            $no1 = 15;
            $no++;
            $sheet->setCellValue('B'.$no++,"TOTAL PENGELUARAN");
            $sheet->setCellValue('C14', $expenditure1);
            $sheet->setCellValue('B'.$no++,"RUGI / LABA");
            $sheet->setCellValue('C15', $income_amount - $expenditure1);


            $filename='Laporan_Rugi_Laba_'.$start_date.'_s.d._'.$end_date.'.xls';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
            $writer->save('php://output');
        // }else{
        //     echo "Maaf data yang di eksport tidak ada !";
        // }
    }

}