<?php

namespace App\Nova;

use App\Nova\Actions\AddCertificateToEnvironment;
use App\Nova\Actions\RenewCertificate;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Fields\Boolean;

class Certificates extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\Models\LetsEncryptCertificate::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('domain')->required(),
            Boolean::make('created')->hideWhenCreating()->hideWhenUpdating(),
            Date::make('last_renewed_at')->hideWhenCreating()->hideWhenUpdating(),
            Text::make('fullchain_path')->required()->hideFromIndex()->hideWhenCreating()->hideWhenUpdating(),
            Text::make('chain_path')->required()->hideFromIndex()->hideWhenCreating()->hideWhenUpdating(),
            Text::make('cert_path')->required()->hideFromIndex()->hideWhenCreating()->hideWhenUpdating(),
            Text::make('privkey_path')->required()->hideFromIndex()->hideWhenCreating()->hideWhenUpdating(),

        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [
            new RenewCertificate(),
            new AddCertificateToEnvironment()
        ];
    }
}
