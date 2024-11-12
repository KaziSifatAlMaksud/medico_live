<div class="row">
    <div class="col-md-4">
        <div class="row">
            <input type="hidden" name="id" value="" />
            <div class="col-md-12">
                <div class="form-group">
                    <label>{{ (__('notification.lbl_type')) }} : <span class="text-danger">*</span></label>
                    <select name="type" class="form-control select2" id="type" onchange="onChangeType()" data-ajax--url="{{ route('backend.notificationtemplates.ajax-list',['type' => 'constants_key','data_type' => 'notification_type']) }}" data-ajax--cache="true" required disabled>
                        @if(isset($data->type))
                        <option value="{{ $data->type }}" selected>{{ optional($data->constant)->name ?? '' }}</option>
                        @endif
                    </select>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>{{ (__('notification.parameters')) }} :</label><br>
                    <div class="main_form">
                        @if(isset($buttonTypes))
                        @include('notificationtemplate::backend.notificationtemplates.perameters-buttons',['buttonTypes' => $buttonTypes])
                        @endif
                    </div>
                </div>
            </div>
            @php $status = $data->status ?? 0; @endphp
            <div class="col-md-12">
                <div class="form-group">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label" for="category-status">{{ __('messages.status') }} :</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" value="1" name="status" id="category-status" type="checkbox" {{ $status ? 'checked' : '' }} />
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>{{ (__('notification.lbl_to')) }} :</label><br>
                    <select name="to[]" class="form-control select2" data-ajax--url="{{ route('backend.notificationtemplates.ajax-list',['type' => 'constants_key','data_type' => 'notification_to']) }}" data-ajax--cache="true" multiple>
                        @if(isset($data))
                        @if($data->to != null)
                        @foreach(json_decode($data->to) as $to)
                        <option value="{{$to}}" selected="">{{$to}}</option>
                        @endforeach
                        @endif
                        @endif
                    </select>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>{{ (__('notification.lbl_bcc')) }} :</label><br>
                    <select class="form-control select2-tag" name="bcc[]" multiple="">
                        @if(isset($data))
                        @if($data->bcc != null)
                        @foreach(json_decode($data->bcc) as $bcc)
                        <option value="{{$bcc}}" selected="">{{$bcc}}</option>
                        @endforeach
                        @endif
                        @endif
                    </select>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label>{{ (__('notification.lbl_cc')) }} :</label><br>
                    <select class="form-control select2-tag" name="cc[]" multiple="">
                        @if(isset($data))
                        @if($data->cc != null)
                        @foreach(json_decode($data->cc) as $cc)
                        <option value="{{$cc}}" selected="">{{$cc}}</option>
                        @endforeach
                        @endif
                        @endif
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8 ">
        <div class="row pl-3">
            <div class="col-md-12">
                <div class="form-group">
                    <label class="float-left">{{ (__('messages.subject')) }} :</label>
                    <input type="text" name="defaultNotificationTemplateMap[subject]" class="form-control" value="{{ old('defaultNotificationTemplateMap.subject', optional($data->defaultNotificationTemplateMap)->subject ?? '') }}"/>

                    <input type="hidden" name="defaultNotificationTemplateMap[status]" value="1" class="form-control" />
                </div>

                <div class="text-left">
                    <label>{{ (__('messages.template')) }} :</label>
                    <input type="hidden" name="defaultNotificationTemplateMap[language]" value="en" class="form-control" />
                </div>
                <div class="form-group">
                <textarea name="defaultNotificationTemplateMap[template_detail]" class="form-control textarea" id="mytextarea">{{ old('defaultNotificationTemplateMap.template_detail', optional($data->defaultNotificationTemplateMap)->template_detail ?? '') }}</textarea>
                </div>
                <div class="form-group">
                    <label class="float-left">{{ (__('messages.notification_body')) }} :</label>
                    <input type="text" name="defaultNotificationTemplateMap[notification_message]" value="{{ old('defaultNotificationTemplateMap.notification_message', optional($data->defaultNotificationTemplateMap)->notification_message ?? '') }}" id="en-notification_message" class="form-control notification_message" />

                </div>
                <div class="form-group">
                    <label class="float-left">{{ (__('messages.notification_link')) }} :</label>
                    <input type="text" name="defaultNotificationTemplateMap[notification_link]" value="{{ old('defaultNotificationTemplateMap.notification_link', optional($data->defaultNotificationTemplateMap)->notification_link ?? '') }}" id="en-notification_link" class="form-control notification_link" />

                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 pt-2">
            <button onclick="window.history.back();" class="btn btn-outline-primary"><i class="fa-solid fa-angles-left"></i> {{ (__('messages.close'))}}<i class="md md-lock-open"></i></button>
            <button type="submit" class="btn btn-primary"><i class="far fa-save"></i> {{ (__('messages.save'))}}<i class="md md-lock-open"></i></button>
        </div>
    </div>
</div>
<script>
    function onChangeType(url, render) {
        var dropdown = document.getElementById("type");
        var selectedValue = dropdown.value;
        var url = "{{ route('backend.notificationtemplates.notification-buttons',['type' => 'buttonTypes']) }}";
        $.get(url, function(data) {
            var html = data;
            console.log(html);
            if (render !== undefined && render !== '' && render !== null) {
                $('.' + render).html(html);
            } else {
                $(".main_form").html(html);
                $("#formModal").modal("show");
            }
        });
    }
</script>