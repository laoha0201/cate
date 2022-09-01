<?php

namespace Laoha\Cate\Models;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Spatie\EloquentSortable\Sortable;
use Dcat\Admin\Traits\HasDateTimeFormatter;

class Cate extends Model implements Sortable
{
    use Traits\ModelTree;
    use HasDateTimeFormatter;
    use SoftDeletes;

    const CACHE_TAG = 'cate:';


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cate';

    /**
     * 允许批量赋值的属性
     * @var array
     */
    protected $fillable = [
        'parent_id', 'parents', 'base', 'name', 'slug', 'order', 'thumb', 'allow_publish', 'parents', 'keywords', 'desc', 'allow_comment'
    ];

    /**
     * 属性类型转换
     *
     * @var array
     */
    protected $casts = [
        'ext' => 'array',
    ];

    /**
     * 排序字段
     * @var array
     */
    protected $sortable = [
        // 设置排序字段名称
        'order_column_name' => 'order',
        // 是否在创建时自动排序，此参数建议设置为true
        'sort_when_creating' => true,
    ];

    /**
     * 标题字段
     * @var string
     */
    protected $titleColumn = 'name';

    protected $base;

    public function __construct($base = null)
    {
        if ($base && is_string($base)) {
            $this->base = $base;
            static::addGlobalScope('avaiable', function (Builder $builder) use ($base) {
                $builder->where('base', $base);
            });
            parent::__construct();
        } else {
            parent::__construct();
        }
    }





    /**
     * Perform any actions required before the model boots.
     *
     * @return void
     */
    protected static function booting()
    {
        /*static::creating(function ($model) {
            if (!$model->slug) {
                $model->slug = (new Pinyin)->permalink($model->name);
            }
        });
		*/
        static::forceDeleted(function ($model) {
            if (!$model->thumb) {
                $storage = Storage::disk(config('admin.upload.disk'));
                $storage->destroy($model->thumb);
            }
        });
    }

    /**
     * Get the children relation.
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(static::class, 'parent_id', 'id');
    }

    /**
     * Get the parent relation.
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class);
    }

    /**
     * 查找指定类型栏目
     * @param Builder $query
     * @return Builder
     */
    public function scopeBase(Builder $query, $base): Builder
    {
        return $query->where('base', $base);
    }

    /**
     * 查找顶级栏目
     * @param Builder $query
     * @return Builder
     */
    public function scopeRoot(Builder $query): Builder
    {
        return $query->where('parent_id', 0);
    }

    /**
     * 获取子栏目
     * @return array
     */
    public function getChildrenIds(): array
    {
        return $this->children()->pluck('id')->all();
    }


    /**
     * 删除缓存
     * @param int $id
     */
    public static function forgetCache(int $id): void
    {
        Cache::forget(static::CACHE_TAG . $id);
    }

    /**
     * 通过ID获取内容
     * @param int $id
     * @return Cate
     */
    public static function findById(int $id): Cate
    {
        return Cache::rememberForever(static::CACHE_TAG . $id, function () use ($id) {
            return static::query()->find($id);
        });
    }

    /**
     * 获取顶级栏目下拉数据
     * @param string $base
     * @return Collection
     */
    public static function getRootSelect(string $base): Collection
    {
        return static::base($base)->root()->select(['id', 'name'])->orderBy('order')->pluck('name', 'id');
    }

    /**
     * 获取顶级栏目
     * @param string $base
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getRootNodes(string $base)
    {
        return static::base($base)->root()->select(['id', 'name'])->orderBy('order')->get();
    }



    /**
     * Get options for Select field in form.
     *
     * @param  \Closure|null  $closure
     * @param  string  $rootText
     * @return array
     */
    public static function selectBaseOptions($base = '')
    {
        return self::selectOptions(function ($query) use ($base) {
            return $query->where('base', $base);
        });
    }


    /**
     * Sets groups.
     * @param string $values
     */
    public function setGroupsAttribute($values)
    {
        if (!empty($values)) {
            return $this->attributes['groups'] = implode(',', preg_split(
                '/\s*,|，\s*/u',
                preg_replace('/\s+/u', ' ', is_array($values) ? implode(',', $values) : $values),
                -1,
                PREG_SPLIT_NO_EMPTY
            ));
        }
    }

    /**
     * Get options for Select field in form with publish.
     *
     * @param  \Closure|null  $closure
     * @param  string  $rootText
     * @return array
     */
    public static function selectByOptions(Builder $query, $base = '')
    {
        return $query->where('base', $base)->orderBy('order');
    }
}
