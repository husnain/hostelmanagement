<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Room;
use Illuminate\Http\RedirectResponse;
use App\RoomCategory;
use App\Tenant;
use DB;
use App\RoomAllocation;


class RoomController extends Controller
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
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $abc = Auth->tenant();
        // dd($abc);
        $rooms = Room::paginate(10);
        return view('rooms.index', compact('rooms'));
    }

    public function create()
    {
        $roomsCategory = RoomCategory::pluck('name','id');
        return view('rooms.create', compact('roomsCategory'));
    }
    

    public function store(Request $request)
    {
        // dd($request->all());exit();
        try{
            $rooms = new Room($request->all());
            $rooms->user_id = $request->user()->id;
            $rooms->save();
            \Session::flash('success','New Room have been added.');
            return redirect()->route('roomIndex');
        }catch(Exception $e){
            report($e);
            return false;
        }
    }

    public function edit($id)
    {
        $room = Room::find($id);
        $roomsCategory = RoomCategory::pluck('name','id');
        return view('rooms.edit', compact('room', 'roomsCategory'));
    }

    public function delete($id)
    {
        $room_allocation = RoomAllocation::where('room_id', $id)->get();
        $room = Room::find($id);
        $room->is_active = 0;
        if($room->save()){
            \Session::flash('success', 'Room have been deleted.');
        }else{
            \Session::flash('error', 'Unable to delete room.');
        }
        return redirect()->back();
    }

    public function update(Request $request)
    {
        $room = Room::find($request->id);
        $room->name = $request->name;
        $room->number = $request->number;
        $room->description = $request->description;
        $room->strength = $request->strength;
        $room->room_category_id = $request->room_category_id;
        $room->is_active = $request->is_active;
        if($room->save()){
            \Session::flash('success', 'Room have been updated.');
        }else{
            \Session::flash('error', 'Unable to update room.');
        }
        return redirect()->back();
    }

    public function rentView($id, $room_id)
    {
        $tenants_of_room = DB::table('room_allocation as ra')
        ->leftjoin('tenants as t', 'ra.tenant_id', 't.id')
        ->where('ra.id', $id)
        ->where('ra.room_id', $room_id)
        ->select(DB::raw('count(tenant_id) as total_tenants'))
        ->first();
        $roomAllocation = DB::table('room_allocation')
        ->where('room_id', $room_id)->get();
        $room = Room::find($room_id);
        $rent = $room->rent / $tenants_of_room->total_tenants;
        \Session::flash("success", "Rent has been divided");
        return redirect()->back();
    }
}
