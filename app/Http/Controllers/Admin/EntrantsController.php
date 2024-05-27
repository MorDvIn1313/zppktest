<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EntraintSubject;
use Illuminate\Http\Request;
use App\Models\Entrant;
use App\Models\Spetsialnost;
use App\Models\Subject;
use App\Models\User;

class EntrantsController extends Controller
{
    public function index() {
        $entrants = Entrant::all();
        return view('admin.entrants.index', compact('entrants'));
    }

    public function create() {
        return view('admin.entrants.create');
    }

    public function edit(Entrant $entrant) { 
        $spets = Spetsialnost::all();

        $subj_ent = EntraintSubject::where('entrant_id', $entrant['user_id'])->get();
        $subj = Subject::all();
        
        return view('admin.entrants.edit', compact('entrant', 'spets', 'subj_ent', 'subj'));
    }

    public function store(Request $request)
    {
        dd("Абитуриент добавлен");
    }

    public function update(Request $request, Entrant $entrant)
    {
        $ocenca = $request->ocenkas;
        $subj_ent = Subject::all();
        $subj_num = [];
        foreach ($subj_ent as $subj) {
            array_push($subj_num, $subj['id']);
        };
        
        $ocen_data = $request->validate([
                'entrant_id' => 'nullable|string',
                'subject_id' => 'nullable|string',
                'ocenka' => 'nullable|string',
        ]);

        $i = 0;
        foreach ($ocenca as $oc) {
            $ocen_data['entrant_id'] = $entrant->user_id;
            $ocen_data['subject_id'] = $subj_num[$i];
            $ocen_data['ocenka'] = $oc;
            EntraintSubject::updateOrCreate(['entrant_id' => $entrant->user_id, 'subject_id' => $subj_num[$i]], $ocen_data);
            $i++;
        }

        $entr = User::find($entrant['user_id']);
        if(!$entr) {
            return abort(404);
        }

        $data = $request->validate([
            'name' => 'required|string',
            'family' => 'required|string',
            'patronymic' => 'required|string',
            'passport' => 'nullable|file',
            'passport_seria' => ['nullable', 'string', 'regex:/^\d{4}$/'],
            'passport_number' => ['nullable', 'string', 'regex:/^\d{6}$/'],
            'vkontakte' => 'required|string',
            'spetsialnost' => 'nullable|integer',
            'rating' => 'nullable|integer',
            'sirota' => 'nullable|integer',
            'outregion' => 'nullable|integer',
            'passport_propiska' => 'nullable|string',
            'passport_dr' => 'nullable|string',
            'document_on_education' => 'nullable|file',
            'document_on_education_name' => 'nullable|string',
            'document_on_education_year' => 'nullable|string',
            'snils' => 'nullable|file',
            'snils_number' => 'nullable|string',
            'medical_certificate' => 'nullable|file',
            'vaccination_certificate' => 'nullable|file',
            'phone' => 'nullable|string',
        ]);

        try {
            $entr->update($data);
            $entrant->update($data);
        } catch (\Exception $exception) {            
            return $exception->getMessage();
        }
        return redirect()->route('entrant.index');
    }

    public function destroy(Entrant $entrant)
    {   
        $ent = User::find($entrant['user_id']);
        if(!$ent) {
            return abort(404);
        }

        $entrant->delete();
        $ent->delete();
        return redirect()->route('entrant.index');
    }

    public function ocenka(Request $request, Entrant $entrant)
    {   
        $subj_ent = EntraintSubject::where('entrant_id', $entrant['user_id'])->get();
        $subj_ocenka = 0;
        foreach ($subj_ent as $subj) {
            $subj_ocenka += $subj['ocenka'];
        };
        
        dd("Сумма оценок = " . $subj_ocenka);
    }
}