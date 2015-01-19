{{ Form::errors($errors) }}

@if (isset($model))
    {{ Form::model($model, ['route' => ['admin.matches.update', $model->id], 'files' => true, 'method' => 'PUT']) }}
@else
    {{ Form::open(['url' => 'admin/matches', 'files' => true]) }}
@endif
    {{ Form::smartSelect('state', trans('app.state'), $modelClass::$states); }}

    {{ Form::smartSelectRelation('leftTeam', trans('matches::left_team'), $modelClass) }}

    {{ Form::smartSelectRelation('rightTeam', trans('matches::right_team'), $modelClass) }}

    {{ Form::smartSelectRelation('game', 'Game', $modelClass) }}

    {{ Form::smartSelectRelation('tournament', 'Tournament', $modelClass) }}

    {{ Form::smartUrl('url', trans('app.url')) }}

    {{ Form::smartText('broadcast', trans('matches::broadcast')) }}

    {{ Form::smartText('left_lineup', trans('matches::left_lineup')) }}

    {{ Form::smartText('right_lineup', trans('matches::right_lineup')) }}

    {{ Form::smartTextarea('text', trans('app.description'), true) }}

    {{ Form::smartDateTime('played_at', trans('matches::played_at')) }}
    
    {{ Form::smartCheckbox('featured', trans('app.featured')) }}

    {{-- We can't add scores to a match that doesn't actually exist --}}
    @if (isset($model))
        {{ Form::smartGroupOpen(null, 'Match Scores') }}
            <div class="scores">
                @foreach ($model->match_scores as $matchScore)
                    @include('matches::admin_map', compact('matchScore'))
                @endforeach

                <span class="add-new">+</span>
            </div>
        {{ Form::smartGroupClose() }}
    @endif

    {{ Form::actions() }}
{{ Form::close() }}

<script>
    $(document).ready(function()
    {
        function loadLeftLineup()
        {
            var id = $(this).val();
            $.get(contentify.baseUrl + 'admin/teams/' + id + '/lineup', function(data)
            {
                $('#left_lineup').val(data);
            });
        }

        function loadRightLineup()
        {
            var id = $(this).val();
            $.get(contentify.baseUrl + 'admin/opponents/' + id + '/lineup', function(data)
            {
                $('#right_lineup').val(data);
            });
        }

        // "select#..." adresses the proper element when using Formstone\Selecter
        $('select#_relation_leftTeam').change(loadLeftLineup);
        $('select#_relation_rightTeam').change(loadRightLineup);

        //if (! isset($model))
        //$('select#_relation_leftTeam').change();
        //$('select#_relation_rightTeam').change();
        //endif
        
        {{-- We can't add scores to a match that doesn't actually exist --}}
        @if (isset($model))

        var template = '<div class="boxer-plain add-new"> {{ Form::smartSelectForeign('map_id', 'Map') }} {{ Form::smartGroupOpen('left_score', trans('matches::score')) }} <input type="text" name="left_score" style="width: 20px" value="%%scoreLeft%%"> : <input type="text" name="right_score" style="width: 20px" value="%%scoreRight%%"> {{ Form::smartGroupClose() }}</div>';

        contentify.templateManager.add('mapForm', template);

        $('.scores .add-new').click(function()
        {
            var $el = $(this);
            var compiled = contentify.templateManager.get('mapForm', {scoreLeft: 0, scoreRight: 0});

            $.boxer($(compiled).append(
                $('<button>').text('{{ trans('app.save') }}').click(function()
                {
                    $.ajax({
                        url: contentify.baseUrl + 'admin/matches/scores/store',
                        type: 'POST',
                        data: {
                            match_id:       "{{ $model->id }}",
                            map_id:         $('#map_id').val(),
                            left_score:     $('input[name=left_score]').val(),
                            right_score:    $('input[name=right_score]').val(),
                        }
                    }).done(function(data) 
                    {
                        $el.before(data);
                    }).fail(function(response)
                    {
                        contentify.alertRequestFailed(response);
                    });
                    $.boxer('close');
                })
            ));
        });

        $('.page').on('click', '.scores .item', function()
        {
            var $el = $(this);
            var id = $el.attr('data-id');

            var compiled = contentify.templateManager.get('mapForm', 
                {scoreLeft: $el.attr('data-left-score'), scoreRight: $el.attr('data-right-score')});

            var $compiled = $(compiled);
            $compiled.find('select').val($el.attr('data-map-id'));

            $.boxer($compiled.append(
                $('<button>').text('{{ trans('app.save') }}').click(function()
                {
                    $.ajax({
                        url: contentify.baseUrl + 'admin/matches/scores/' + $el.attr('data-id'),
                        type: 'PUT',
                        data: {
                            map_id:         $('#map_id').val(),
                            left_score:     $('input[name=left_score]').val(),
                            right_score:    $('input[name=right_score]').val(),
                        }
                    }).done(function(data) 
                    {
                        $el.replaceWith(data);
                    }).fail(function(response)
                    {
                        contentify.alertRequestFailed(response);
                    });
                    $.boxer('close');
                })
            ).append(
                $('<button>').text('{{ trans('app.delete') }}').click(function()
                {
                    $.ajax({
                        url: contentify.baseUrl + 'admin/matches/scores/' + id,
                        type: 'DELETE'
                    }).done(function(data) 
                    {
                        $el.remove();
                    }).fail(function(response)
                    {
                        contentify.alertRequestFailed(response);
                    });
                    $.boxer('close');
                })
            ));
        });

        @endif        

    });
</script>