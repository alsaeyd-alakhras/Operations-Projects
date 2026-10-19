<div class="row">
    <div class="form-group p-3 col-4">
        <x-form.input label="اسم الفرع" :value="$branch->name" name="name" placeholder="المغازي" required autofocus/>
    </div>
</div>
<div class="row align-items-center mb-2">
    <div class="col">
        <h2 class="h5 page-title"></h2>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary">
            {{$btn_label ?? "أضف"}}
        </button>
    </div>
</div>
