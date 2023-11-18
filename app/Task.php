<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Task extends Model
{
    protected $table = 'task';

    protected $hidden = ['creator', 'created_at', 'updated_at', 'updater'];

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $touches = ['matter'];

    protected $casts = [
        'due_date' => 'date:Y-m-d',
        'done_date' => 'date:Y-m-d',
    ];

    // This is moved to the task's store() method, allowing easier programmatic date updates
    // public function setDueDateAttribute($value)
    // {
    //     $locale = Carbon::getLocale();
    //     $this->attributes['due_date'] = Carbon::createFromLocaleIsoFormat('L', $locale, $value);
    // }

    // public function setDoneDateAttribute($value)
    // {
    //     $locale = Carbon::getLocale();
    //     $this->attributes['done_date'] = Carbon::createFromLocaleIsoFormat('L', $locale, $value);
    // }

    public function info()
    {
        return $this->belongsTo(\App\EventName::class, 'code');
    }

    public function trigger()
    {
        return $this->belongsTo(\App\Event::class, 'trigger_id');
    }

    public function matter()
    {
        return $this->hasOneThrough(\App\Matter::class, \App\Event::class, 'id', 'id', 'trigger_id', 'matter_id');
    }

    public function rule()
    {
        return $this->hasMany(\App\Rule::class, 'id', 'rule_used');
    }

    public static function getUsersOpenTaskCount()
    {
        $userid = Auth::user()->id;
        $role = Auth::user()->default_role;
        $selectQuery = Task::join('event as e', 'task.trigger_id', 'e.id')
            ->join('matter as m', 'e.matter_id', 'm.id')
            ->select(
                DB::raw('count(*) as no_of_tasks'),
                DB::raw('MIN(task.due_date) as urgent_date'),
                DB::raw('ifnull(task.assigned_to, m.responsible) as login')
            )
            ->where([
                ['m.dead', 0],
                ['task.done', 0],
            ])
            ->groupby('login');

        if ($role == 'CLI') {
            $selectQuery->join('matter_actor_lnk as cli', 'cli.matter_id', DB::raw('ifnull(m.container_id, m.id)'))
                ->where([
                    ['cli.role', 'CLI'],
                    ['cli.actor_id', $userid],
                ]);
        }

        return $selectQuery->get();
    }

    public function openTasks($renewals, $what_tasks, $user_dashboard)
    {
        // $what_tasks, by default 0, is changed to 1 to see the "assigned_to" tasks or to the id of the client to see client specific tasks
        $tasks = $this->select('task.id', 'en.name', 'task.detail', 'task.due_date', 'event.matter_id', 'matter.uid', 'tit.value as title', 'tm.value as trademark')
            ->join('event_name as en', 'task.code', 'en.code')
            ->join('event', 'task.trigger_id', 'event.id')
            ->join('matter', 'event.matter_id', 'matter.id')
            ->leftJoin('classifier as tit', function ($j) {
                $j->on('tit.matter_id', DB::raw('ifnull(matter.container_id, matter.id)'))
                    ->where([
                        ['tit.type_code', 'TIT'],
                        ['tit.display_order', 1],
                    ]);
            })
            ->leftJoin('classifier as tm', function ($j) {
                $j->on('tm.matter_id', DB::raw('ifnull(matter.container_id, matter.id)'))
                    ->where('tm.type_code', 'TM');
            })
            ->where([
                ['task.done', 0],
                ['matter.dead', 0],
            ]);

        if ($what_tasks == 1) {
            $tasks->where('assigned_to', Auth::user()->login);
        }

        // A client is defined for querying the tasks
        if ($what_tasks > 1) {
            $tasks->join('matter_actor_lnk as cli', 'cli.matter_id', DB::raw('ifnull(matter.container_id, matter.id)'))
                ->where([
                    ['cli.role', 'CLI'],
                    ['cli.actor_id', $what_tasks],
                ]);
        }

        if ($renewals) {
            $tasks->where('task.code', 'REN');
        } else {
            $tasks->where('task.code', '!=', 'REN');
        }

        if (Auth::user()->default_role == 'CLI') {
            $tasks->join('matter_actor_lnk as cli', 'cli.matter_id', DB::raw('ifnull(matter.container_id, matter.id)'))
                ->where([
                    ['cli.role', 'CLI'],
                    ['cli.actor_id', Auth::user()->id],
                ]);
        }

        if ($user_dashboard) {
            $tasks->where(function ($q) use ($user_dashboard) {
                $q->where('matter.responsible', $user_dashboard)
                    ->orWhere('task.assigned_to', $user_dashboard);
            });
        }

        return $tasks->orderby('due_date');
    }

    public static function renewals()
    {
        $query = Matter::select(
            'task.id',
            'task.detail',
            'task.due_date',
            'task.done',
            'task.done_date',
            'event.matter_id',
            DB::raw('IFNULL(fees.cost, task.cost) AS cost'),
            DB::raw('IFNULL(fees.fee, task.fee) AS fee'),
            DB::raw('COALESCE(fees.cost_reduced, fees.cost, task.cost) AS cost_reduced'),
            DB::raw('COALESCE(fees.fee_reduced, fees.fee, task.fee) AS fee_reduced'),
            DB::raw('COALESCE(fees.cost_sup, fees.cost, task.cost) AS cost_sup'),
            DB::raw('COALESCE(fees.fee_sup, fees.fee, task.fee) AS fee_sup'),
            DB::raw('COALESCE(fees.cost_sup_reduced, fees.cost, task.cost) AS cost_sup_reduced'),
            DB::raw('COALESCE(fees.fee_sup_reduced, fees.fee, task.fee) AS fee_sup_reduced'),
            'task.trigger_id',
            'matter.category_code AS category',
            'matter.caseref',
            'matter.uid',
            'matter.country',
            'mcountry.name_FR AS country_FR',
            'mcountry.name AS country_EN',
            'mcountry.name_DE AS country_DE',
            'matter.origin',
            DB::raw('COALESCE(MIN(own.small_entity), MIN(ownc.small_entity), MIN(appl.small_entity), MIN(applc.small_entity)) AS sme_status'),
            'fil.event_date AS fil_date',
            'fil.detail AS fil_num',
            'grt.event_date AS grt_date',
            'event.code AS event_name',
            'event.event_date',
            'event.detail AS number',
            DB::raw("IF(GROUP_CONCAT(DISTINCT ownc.name) IS NOT NULL OR GROUP_CONCAT(DISTINCT own.name) IS NOT NULL,
                CONCAT_WS('; ', GROUP_CONCAT(DISTINCT ownc.name SEPARATOR '; '), GROUP_CONCAT(DISTINCT own.name SEPARATOR '; ')),
                CONCAT_WS('; ', GROUP_CONCAT(DISTINCT applc.name SEPARATOR '; '), GROUP_CONCAT(DISTINCT appl.name SEPARATOR '; '))
            ) AS applicant_name"),
            DB::raw('COALESCE(pa_cli.name, clic.name) AS client_name'),
            DB::raw('COALESCE(pa_cli.address, clic.address) AS client_address'),
            DB::raw('COALESCE(pa_cli.country, clic.country) AS client_country'),
            DB::raw('COALESCE(pa_cli.ren_discount, clic.ren_discount) AS discount'),
            DB::raw('COALESCE(pmal_cli.actor_id, cliclnk.actor_id) AS client_id'),
            DB::raw('COALESCE(pmal_cli.actor_ref, cliclnk.actor_ref) AS client_ref'),
            DB::raw('COALESCE(pa_cli.email, clic.email) AS email'),
            DB::raw('COALESCE(pa_cli.language, clic.language) AS language'),
            'matter.responsible',
            'tit.value AS short_title',
            'titof.value AS title',
            'pub.detail AS pub_num',
            'task.step',
            'task.grace_period',
            'task.invoice_step',
            'matter.expire_date',
            'fees.fee AS table_fee'
        )
            ->leftJoin(
                DB::raw("matter_actor_lnk lappl
            JOIN actor appl ON appl.id = lappl.actor_id
            AND lappl.role = 'APP'"),
                'matter.id',
                'lappl.matter_id'
            )
            ->leftJoin(
                DB::raw("matter_actor_lnk lapplc
            JOIN actor applc ON applc.id = lapplc.actor_id
            AND lapplc.role = 'APP'
            AND lapplc.shared = 1"),
                'matter.container_id',
                'lapplc.matter_id'
            )
            ->leftJoin(
                DB::raw("matter_actor_lnk lown
            JOIN actor own ON own.id = lown.actor_id
            AND lown.role = 'OWN'"),
                'matter.id',
                'lown.matter_id'
            )
            ->leftJoin(
                DB::raw("matter_actor_lnk lownc
            JOIN actor ownc ON ownc.id = lownc.actor_id
            AND lownc.role = 'OWN'
            AND lownc.shared = 1"),
                'matter.container_id',
                'lownc.matter_id'
            )
            ->leftJoin(
                DB::raw('matter_actor_lnk pmal_cli
            JOIN actor pa_cli ON pa_cli.id = pmal_cli.actor_id'),
                function ($join) {
                    $join->on('matter.id', 'pmal_cli.matter_id')->where('pmal_cli.role', 'CLI');
                }
            )
            ->leftJoin(DB::raw('matter_actor_lnk cliclnk
            JOIN actor clic ON clic.id = cliclnk.actor_id'), function ($join) {
                $join->on('matter.container_id', 'cliclnk.matter_id')->where([
                    ['cliclnk.role', 'CLI'],
                    ['cliclnk.shared', 1],
                ]);
            })
            ->leftJoin('country as mcountry', 'mcountry.iso', 'matter.country')
            ->join('event', 'matter.id', 'event.matter_id')
            ->leftJoin(
                'event AS fil',
                function ($join) {
                    $join->on('matter.id', 'fil.matter_id')
                        ->where('fil.code', 'FIL');
                }
            )
            ->leftJoin(
                'event AS pub',
                function ($join) {
                    $join->on('matter.id', 'pub.matter_id')
                        ->where('pub.code', 'PUB');
                }
            )
            ->leftJoin(
                'event AS grt',
                function ($join) {
                    $join->on('matter.id', 'grt.matter_id')
                        ->where('grt.code', 'GRT');
                }
            )
            ->join('task', 'task.trigger_id', 'event.id')
            ->leftJoin(
                'classifier AS tit',
                function ($join) {
                    $join->on(DB::raw('IFNULL(matter.container_id, matter.id)'), 'tit.matter_id')
                        ->where('tit.type_code', 'TIT');
                }
            )
            ->leftJoin(
                'classifier AS titof',
                function ($join) {
                    $join->on(DB::raw('IFNULL(matter.container_id, matter.id)'), 'titof.matter_id')
                        ->where('titof.type_code', 'TITOF');
                }
            )
            ->leftJoin('fees', function ($join) {
                $join->on('fees.for_country', 'matter.country');
                $join->on('fees.for_category', 'matter.category_code');
                $join->on(DB::raw('CAST(task.detail AS UNSIGNED)'), 'fees.qt');
            })
            ->where('task.code', 'REN')
            ->groupBy('task.due_date')
            ->groupBy('task.id')
            ->groupBy('event.matter_id');

        return $query;
    }
}
