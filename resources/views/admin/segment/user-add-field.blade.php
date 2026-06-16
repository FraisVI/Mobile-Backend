<style>
    .user-add-form {
        border: 1px solid #999;
        border-radius: 5px;
        padding: 5px 15px 10px 15px;
        margin-bottom: 20px;
    }
    .user-search-results {
        position: absolute;
        z-index: 100;
        max-height: 280px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        list-style: none;
        margin: 2px 0 0 0;
        padding: 0;
        width: 100%;
    }
    .user-search-results li {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
    }
    .user-search-results li:hover, .user-search-results li.selected {
        background: #f0f0f0;
    }
    .user-search-results li:last-child { border-bottom: none; }
    .user-search-wrap { position: relative; }
</style>

<div class="row">
    <div class="col-lg-6 mb-3">
        <div class="user-add-form">
            <label for="userAddField" class="form-label">Добавление пользователя</label>
            <div class="user-search-wrap">
                <input class="form-control" type="text" id="userAddField" placeholder="Введите имя, телефон или ID карты (минимум 2 символа)...">
                <ul id="userSearchResults" class="user-search-results" style="display: none;"></ul>
            </div>
            <small class="form-text text-muted">Поиск по имени, телефону или client_card_id.</small>
        </div>
        <table id="user-table" class="table">
            <thead>
                <tr>
                    <th></th>
                    <th>ID</th>
                    <th>Пользователь</th>
                </tr>
            </thead>
            <tbody>
                @if (isset($items))
                    <?php /** @var \App\Models\AppUser[] $items */ ?>
                    @foreach($items as $i)
                        <tr data-id="{{ $i->client_card_id }}">
                            <td style="width: 45px;">
                                <input type="hidden" name="card_ids[]" value="{{ $i->client_card_id }}">
                                <button type="button" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash" aria-hidden="true"></i></button>
                            </td>
                            <td>{{ $i->client_card_id }}</td>
                            <td>{{ $i->shortFio() }}</td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>

<script>
    $(document).ready(function() {
        const Item = (id, name) => `
         <tr data-id="${id}">
             <td style="width: 45px;">
                 <input type="hidden" name="card_ids[]" value="${id}">
                 <button type="button" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash" aria-hidden="true"></i></button>
             </td>
             <td>${id}</td>
             <td>${name}</td>
         </tr>
        `;

        const userAddField = $("#userAddField");
        const resultsList = $("#userSearchResults");
        let searchTimeout = null;

        function getExistingIds() {
            return $("#user-table tbody input[name='card_ids[]']").map(function() { return $(this).val(); }).get();
        }

        function addUser(cardId, shortFio) {
            if (getExistingIds().indexOf(cardId) !== -1) return;
            $("#user-table tbody").append(Item(cardId, shortFio));
            userAddField.val("");
            resultsList.hide().empty();
        }

        userAddField.on("input", function() {
            const q = $(this).val().trim();
            clearTimeout(searchTimeout);
            if (q.length < 2) {
                resultsList.hide().empty();
                return;
            }
            searchTimeout = setTimeout(function() {
                $.get("{{ route('segments.search-users') }}", { q: q }, function(data) {
                    resultsList.empty();
                    if (!data || data.length === 0) {
                        resultsList.append("<li class='text-muted'>Ничего не найдено</li>").show();
                        return;
                    }
                    data.forEach(function(u) {
                        const li = $("<li>").attr("data-id", u.client_card_id).attr("data-fio", u.short_fio)
                            .text(u.short_fio + " — " + (u.phone || "") + " (" + u.client_card_id + ")");
                        li.on("click", function() { addUser(u.client_card_id, u.short_fio); });
                        resultsList.append(li);
                    });
                    resultsList.show();
                }).fail(function() { resultsList.hide().empty(); });
            }, 300);
        });

        userAddField.on("keydown", function(e) {
            if (e.which === 13) e.preventDefault();
        });

        $(document).on("click", function(e) {
            if (!$(e.target).closest(".user-search-wrap").length) resultsList.hide();
        });

        $(document).on('click', '.btn-delete', function() {
            $(this).closest('tr').remove();
        });
    });
</script>

