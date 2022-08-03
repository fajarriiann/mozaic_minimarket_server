<?php

namespace App\Http\Controllers;

use App\Models\InvtItem;
use App\Models\InvtItemCategory;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $menus =  User::select('system_menu_mapping.*','system_menu.*')
        ->join('system_user_group','system_user_group.user_group_id','=','system_user.user_group_id')
        ->join('system_menu_mapping','system_menu_mapping.user_group_level','=','system_user_group.user_group_level')
        ->join('system_menu','system_menu.id_menu','=','system_menu_mapping.id_menu')
        ->where('system_user.user_id','=',Auth::id())
        // ->where('system_menu_mapping.company_id', Auth::user()->company_id)
        ->orderBy('system_menu_mapping.id_menu','ASC')
        ->get();

        return view('home',compact('menus'));
    }

    public function selectItemCategory()
    {
        $category = InvtItemCategory::where('data_state',0)
        ->where('company_id', Auth::user()->company_id)
        ->get();
        
        $data = '';
        $data .= "<option value=''>--Choose One--</option>";
        foreach ($category as $mp){
            $data .= "<option value='$mp[item_category_id]'>$mp[item_category_name]</option>\n";	
        }
        return $data;

    }

    public function selectItem($id)
    {
        $item = InvtItem::where('data_state',0)
        ->where('company_id', Auth::user()->company_id)
        ->where('item_category_id', $id)
        ->get();
        
        $data = '';
        $data .= "<option value=''>--Choose One--</option>";
        foreach ($item as $mp){
            $data .= "<option value='$mp[item_id]'>$mp[item_name]</option>\n";	
        }
        return $data;

    }

    public function selectItemUnit($id)
    {
        $unit = InvtItem::join('invt_item_unit','invt_item_unit.item_unit_id','=','invt_item.item_unit_id')
        ->where('invt_item_unit.data_state',0)
        ->where('invt_item.item_id', $id)
        ->where('invt_item.company_id', Auth::user()->company_id)
        ->get();
        
        $data = '';
        $data .= "<option value=''>--Choose One--</option>";
        foreach ($unit as $mp){
            $data .= "<option value='$mp[item_unit_id]'>$mp[item_unit_name]</option>\n";	
        }
        return $data;

    }
}
