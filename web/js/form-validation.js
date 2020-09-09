<!-- Отображение ошибок ввода на форме -->
function viewErrors(form, errors) {
    // Отображение списка ошибок ввода
    $(form + " .error-summary").show();
    // Определение списка ошибок ввода
    var ul = $(".error-summary ul");
    // Удаление из списка ошибок ввода всех сообщений
    ul.each(function(i, item) {
        $(item).find('li').each(function(j, li){
            li.remove();
        });
    });
    // Удаление всех слоев с сообщениями у полей
    $(form + " .help-block").each(function() {
        $(this).remove();
    });
    // Цикл по всем ошибкам ввода
    $.each(errors, function (key, value) {
        // Добавление ошибки ввода в общий список
        var li = document.createElement('li');
        li.innerHTML = value;
        ul.append(li);
        console.log(key + value);
        // Добавление слоя ошибки ввода к полю
        var field = $(form + " #" + key);
        field.after("<div class=\"help-block\">" + value + "</div>");
        field.closest(".form-group").addClass("has-error");
    });
}