<?php

namespace App\Livewire;

use App\Models\Logs;
use App\Models\Record;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TableRecord extends Component
{
    public $records;
    public $count = [
        'count_records' => '',
        'sum_amount' => '',
        'doctor_share' => '',
        'anesthesiologists_share' => '',
        'bed' => '',
        'private' => '',
    ];

    public $show = false;



    public $showModal = false;

    public $dataModal = [
        'id' => '',
        'name' => '',
        'date' => '',
        'patient_ID' => '',
        'age' => '',
        'phone_number1' => '',
        'phone_number2' => '',
        'financier_number' => '',
        'operation' => '',
        'doctor' => '',
        'amount' => '',
        'doctor_share' => '',
        'anesthesiologists_share' => '',
        'anesthesia' => '',
        'bed' => '',
        'private' => '',
        'notes' => '',
        'displacement_status' => '',
        'income_status' => '',
        'family_burden' => '',
        'distributed' => '',
        'done' => '',
    ];

    public $filterData = [
        'name' => '',
        'from_date' => '',
        'to_date' => '',
        'patient_ID' => '',
        'financier_number' => '',
        'operation' => '',
        'doctor' => '',
        'anesthesia' => '',
        'done' => '',
        'displacement_status' => '',
        'income_status' => '',
        'family_burden' => '',
        'distributed' => '',
    ];

    public $financier_numbers;
    public $operations;
    public $doctors;
    public $anesthesias;



    public function __construct()
    {
        $this->financier_numbers = Record::select('financier_number')->distinct()->pluck('financier_number')->toArray();
        $this->operations = Record::select('operation')->distinct()->pluck('operation')->toArray();
        $this->doctors = Record::select('doctor')->distinct()->pluck('doctor')->toArray();
        $this->anesthesias = Record::select('anesthesia')->distinct()->pluck('anesthesia')->toArray();
    }

    public function mount(){
        $this->records = Record::orderBy('date', 'asc')->get();
        $this->count['count_records'] = $this->records->count();
        $this->count['sum_amount'] = $this->records->sum('amount');
        $this->count['doctor_share'] = $this->records->sum('doctor_share');
        $this->count['anesthesiologists_share'] = $this->records->sum('anesthesiologists_share');
        $this->count['bed'] = $this->records->sum('bed');
        $this->count['private'] = $this->records->sum('private');

        $this->filterData['to_date'] = \Carbon\Carbon::now()->format('Y-m-d');
    }


    public function search(){
        $valueS = str_replace('*', '%', $this->filterData['name']);
        $this->records = Record::where('name', 'LIKE', "%{$valueS}%")->orderBy('date', 'asc')->get();

        $this->count['count_records'] = $this->records->count();
        $this->count['sum_amount'] = $this->records->sum('amount');
        $this->count['doctor_share'] = $this->records->sum('doctor_share');
        $this->count['anesthesiologists_share'] = $this->records->sum('anesthesiologists_share');
        $this->count['bed'] = $this->records->sum('bed');
        $this->count['private'] = $this->records->sum('private');
    }

    public function filter(){
        $this->records = Record::query();
        foreach ($this->filterData as $key => $value) {
            if($value != ''){
                if($key == 'from_date'){
                    $value = \Carbon\Carbon::parse($value)->format('Y-m-d');
                    $this->records = $this->records->where('date', '>=', $value);
                }elseif($key == 'to_date'){
                    $value = \Carbon\Carbon::parse($value)->format('Y-m-d');
                    $this->records = $this->records->where('date', '<=', $value);
                }elseif($key == 'financier_number' || $key == 'operation' || $key == 'doctor' || $key == 'anesthesia' || $key == 'done' || $key == 'displacement_status' || $key == 'income_status' || $key == 'family_burden' || $key == 'distributed' || $key == 'management_status'){
                    $this->records = $this->records->where($key, '=', $value);
                }else{
                    $this->records = $this->records->where($key, 'like', '%'.$value.'%');
                }
            }
        }
        $this->records = $this->records->orderBy('date', 'asc')->get();
        $this->count['count_records'] = $this->records->count();
        $this->count['sum_amount'] = $this->records->sum('amount');
        $this->count['doctor_share'] = $this->records->sum('doctor_share');
        $this->count['anesthesiologists_share'] = $this->records->sum('anesthesiologists_share');
        $this->count['bed'] = $this->records->sum('bed');
        $this->count['private'] = $this->records->sum('private');
    }

    public function resetForm(){
        foreach ($this->filterData as $key => $value) {
            $this->filterData[$key] = '';
        }
        $this->records = Record::orderBy('date', 'asc')->get();
        $this->count['count_records'] = $this->records->count();
        $this->count['sum_amount'] = $this->records->sum('amount');
        $this->count['doctor_share'] = $this->records->sum('doctor_share');
        $this->count['anesthesiologists_share'] = $this->records->sum('anesthesiologists_share');
        $this->count['bed'] = $this->records->sum('bed');
        $this->count['private'] = $this->records->sum('private');
    }

    public $name = '';
    public function checkName($name){
        $this->name = $name;
        
    }

    public function render()
    {
        return view('livewire.table-record');
    }
}
