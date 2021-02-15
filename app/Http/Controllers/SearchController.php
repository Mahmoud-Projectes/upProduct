<?php

namespace App\Http\Controllers;

use App\Production;
use App\Employee_names;
use Carbon\Carbon;
use Cassandra\Date;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{
    public function __construct()
    {
        // admin or entry
        $this->middleware('auth');
    }

    public function production()
    {
        return view('production')
            ->with('messageStart', 'يرجى البحث عن الموظف إما بالرقم الوظيفي أو بالاسم.')
            ->with('navProduction', 'active');
    }

    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'search' => 'required|string'
        ],[
            'required' => 'الرجاء ملئ الحقل قبل البحث.'
        ]);

        if($validator->fails())
            return back()
                ->withErrors($validator)->withInput();
        else
        {
            if(filter_var($request->get('search'), FILTER_VALIDATE_INT)) {

                $data = Employee_names::query()->select('id_emp', 'name')->where('id_emp', '=', $request->search)->get();

                if ($data->first())
                    return view('production')
                        ->with('data', $data)
                        ->with('old', $request->get('search'))
                        ->with('navProduction', 'active');
            }else
            {
                // حذف الفراغات وجلب الأحرف فقط
                $listName = explode(" ",$request->get('search'));
                $count = count($listName);

                for ($i = 0; $i < $count;++$i)
                    if(empty($listName[$i]))
                        unset($listName[$i]);

                // reset array keeys
                $listName = array_values($listName);

                // إضافة حرف + من أجل البحث في قاعدة البيانات
                $tempListName = array_fill(0, count($listName), NULL);
                for ($i = 0; $i < count($listName);++$i)
                    $tempListName[$i] .= '+' . $listName[$i];

                // convert array to string
                $strSearch = implode(" ", $tempListName);

                $sql = "MATCH(name) AGAINST('. $strSearch .' IN BOOLEAN MODE) limit 25";

                $data = Employee_names::query()->select()->whereRaw($sql)->get();

                if ($data->first())
                    return view('production')
                        ->with('data', $data)
                        ->with('old', $request->get('search'))
                        ->with('navProduction', 'active');
            }
        }
        return view('production')
            ->with('messageStart', 'لايوجد موظف بهذا الاسم أو الرقم الوظيفي.')
            ->with('data', null)
            ->with('old', $request->get('search'));
    }

    public function store(Request $request)
    {
        // max:4 when numeric -> number      <= 4
        // max"4 when string  -> char length <= 4
        $validator = Validator::make($request->all(), [
            'id_emp'     => 'required|numeric|max:4294967295',
            'production' => 'required|numeric|max:4294967295'
        ],[
            'required' => 'الرجاء قم بملئ الحقل.',
            'numeric' => 'الرجاء أدخل قيمة رقمية فقط.',
            'max' => 'الرقم الذي أدخلته أكبر من المتوقع يرجى إدخال رقم أقل',
        ]);

        $dataColl = collect([['id_emp' => $request->get('id_emp'),
                             'name'   => $request->get('name'),
                             'production'   => $request->get('production')]]);

        if($validator->fails())
            return view('production')
                ->with('data', $dataColl)
                ->with('old', $request->get('search'))
                ->withErrors($validator)
                ->with('navProduction', 'active');
        else
        {
            // كائن يقوم بإعطاء الوقت
            $carbon = new Carbon();
            // جلب الصف المضاف اليوم فقط
            $data = Production::query()
                ->where('id_emp', '=', $request->id_emp)
                ->whereBetween('updated_at',[
                    $carbon->toDateString() . ' 00:00:00',
                    $carbon->toDateString() . ' 23:59:59'
                    ]);
            if($data !== null) {
                $msg = '';
                $state = true;
                if ($data->count() > 0) {

                    // القيمة القديمة قبل التحديث من أجل طرحها من قيمة الانتاج الكلي واضافة القيمة الجديدة
                    $oldValue = $data->select('production')->get()->first()['production'];

                    $data->update([
                        'id_emp' => $request->get('id_emp'),
                        'production' => $request->get('production')]);

                    // إضافة قيمة الإنتاج إلى الانتاج الكلي في جدول الأسماء
                    $query = Employee_names::query()->where('id_emp',$request->id_emp);
                    $allValue = $query->select('all_production')->first()['all_production'];

                    $query->update([
                        'all_production' => $allValue - $oldValue + $request->production
                    ]);

                    $msg = 'تم تحديث انتاج الموظف ' . $request->get('name') . ' إلى ' . $request->get('production');
                } else {
                        Production::query()->create(
                            ['production' => $request->production,
                                'id_emp' => $request->id_emp]);

                    // إضافة قيمة الإنتاج إلى الانتاج الكلي في جدول الأسماء
                    $query = Employee_names::query()->where('id_emp',$request->id_emp);
                    $value = $query->select('all_production')->first()['all_production'];

                    $query->update([
                        'all_production' => $value + $request->production
                    ]);

                    $msg = 'تم إضافة قيمة الإنتاج للموظف ' . $request->get('name') . ' ،وتساوي: ' . $request->get('production');
                    $state = false;
                }
                return view('production')
                    ->with('data', $dataColl)
                    ->with('old', $request->get('search'))
                    ->with(['message'=> $msg])
                    ->with('state', $state)
                    ->with('navProduction', 'active');

            }
        }
        return view('production')->withErrors(['خطأ، لم يتم تحديث المعلومات.'])->with('navProduction', 'active');;
    }

    public function storeGet()
    {
        return redirect('/');
    }
}
