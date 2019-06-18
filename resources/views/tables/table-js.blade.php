<script>
    var relatedUrl = ""; // Identifies what to display in the Ajax-filled modal. Updated according to the href attribute used for triggering the modal
    var sourceUrl = "";  // Identifies what to reload when refreshing the list

    function refreshRuleList() {
        var url = sourceUrl + '?' + $("#filter").find("input").filter(function () {
            return $(this).val().length > 0;
        }).serialize(); // Filter out empty values
        $('#rule-list').load(url + ' #rule-list > tr', function () { // Refresh all the tr's in tbody#matter-list
            window.history.pushState('', 'phpIP', url);
        });
    }

    $(document).ready(function () {

        // Reload the rules list when closing the modal window
        $("#ajaxModal").on("hidden.bs.modal", function (event) {
            refreshRuleList();
        });

        // Display the modal view for creation of record
        $("#addModal").on("show.bs.modal", function (event) {
            relatedUrl = $(event.relatedTarget).attr("href");
            sourceUrl = $(event.relatedTarget).data("source");   // Used to refresh the list
            resource = $(event.relatedTarget).data("resource");
            $(this).find(".modal-title").text($(event.relatedTarget).attr("title"));
            $(this).find(".modal-body").load(relatedUrl);
        });
        // Reload the rules list when closing the modal window
        $("#addModal").on("hidden.bs.modal", function (event) {
            refreshRuleList();
        });
    });

// Notes edition
    /*$("#ajaxModal").on("keyup", "textarea.noformat", function () {
        var field = $(this).data('field');
        $(field).removeClass('hidden-action');
        $(this).addClass('changed');
    });

    $("#ajaxModal").on("click", "button.area", function () {
        var field = $(this).data('field');
        var areaId = '#' + field;
        if ($(areaId).hasClass('changed')) {
            $.ajax({
                type: 'PUT',
                url: $(this).closest("table").data("source") + $(this).closest("table").data("id"),
                data: field + "=" + $(areaId).val()
            });
            $(this).addClass('hidden-action');
            $(areaId).removeClass('changed');
        }
        return false;
    });*/

    $('.fiter-input').keyup(debounce(function () {
        if ($(this).val().length !== 0)
            $(this).css("background-color", "bisque");
        else
            $(this).css("background-color", "white");
        sourceUrl = $(this).data("source");   // Used to refresh the list
        refreshRuleList();
    }, 500));

// Specific in place edition of rule
    $('#ajaxModal').on("click", 'input[type="radio"]', function () {
        var mydata = {};
        mydata[this.name] = this.value;
        mydata['_method'] = "PUT";
        $.post(resource + $(this).closest("table").data("id"), mydata)
            .done(function () {
                $("#ajaxModal").find(".modal-body").load(relatedUrl);
            });
    });

    $('#rule-list').on("click", '.delete-from-list', function () {
        var del_conf = confirm("Deleting rule " + $(this).closest("tr").data("id") + " from table?");
        if (del_conf === 1) {
            var data = $.param({_method: "DELETE"});
            $.post('/rule/' + $(this).closest("tr").data("id"), data).done(function () {
                sourceUrl = "/rule?";  // Used to refresh the list
                refreshRuleList();
            });
        }
        return false;
    });

    $('#rule-list').on("click", '.delete-event-name', function (event) {
        var del_conf = confirm("Deleting event name from table?");
        if (del_conf === 1) {
            var data = $.param({_method: "DELETE"});
            $.post('/eventname/' + $(this).closest("tr").data("id"), data).done(function () {
                $('#ajaxModal').find(".modal-body").load(relatedUrl);
            });
            sourceUrl = $(this).data("source");  // Used to refresh the list
            refreshRuleList();
        }
        return false;
    });

    $('#ajaxModal').on("click", '#delete-rule', function () {
        var del_conf = confirm("Deleting rule from table?");
        if (del_conf === 1) {
            var data = $.param({_method: "DELETE"});
            $.post('/rule/' + $(this).data("id"), data).done(function () {
                $('#ajaxModal').find(".modal-body").load(relatedUrl);
            });
        }
        return false;
    });


    $('#ajaxModal').on("click", '#delete-ename', function () {
        var del_conf = confirm("Deleting event name from table?");
        if (del_conf === 1) {
            var data = $.param({_method: "DELETE"});
            $.post('/eventname/' + $(this).data("id"), data).done(function () {
                $('#ajaxModal').find(".modal-body").load(relatedUrl);
            });
        }
        return false;
    });

// For creation rule modal view

    $('#addModal').on("click", 'input[name="task_new"]', function () {
        $(this).autocomplete({
            minLength: 1,
            source: "/event-name/autocomplete/1",
            change: function (event, ui) {
                if (!ui.item)
                    $(this).val("");
            },
            select: function (event, ui) {
                event.preventDefault();
                $(this).val(ui.item.label);
                $("input[name='task']").val(ui.item.value);
            }
        });
    });

    $('#addModal').on("click", 'input[name$="country_new"]', function () {
        $(this).autocomplete({
            minLength: 1,
            source: "/country/autocomplete",
            change: function (event, ui) {
                if (!ui.item)
                    $(this).val("");
            },
            select: function (event, ui) {
                event.preventDefault();
                $(this).val(ui.item.value);
                $("input[name$='country']").val(ui.item.id);
            }
        });
    });

    $('#addModal').on("click", 'input[name="for_origin_new"]', function () {
        $(this).autocomplete({
            minLength: 1,
            source: "/country/autocomplete",
            change: function (event, ui) {
                if (!ui.item)
                    $(this).val("");
            },
            select: function (event, ui) {
                event.preventDefault();
                $(this).val(ui.item.value);
                $("input[name='for_origin']").val(ui.item.id);
            }
        });
    });

    $('#addModal').on("click", 'input[name$="category_new"]', function () {
        $(this).autocomplete({
            minLength: 1,
            source: "/category/autocomplete",
            change: function (event, ui) {
                if (!ui.item)
                    $(this).val("");
            },
            select: function (event, ui) {
                event.preventDefault();
                $(this).val(ui.item.value);
                $("input[name$='category']").val(ui.item.id);
            }
        });
    });

    $('#addModal').on("click", 'input[name="for_type_new"]', function () {
        $(this).autocomplete({
            minLength: 1,
            source: "/type/autocomplete",
            change: function (event, ui) {
                if (!ui.item)
                    $(this).val("");
            },
            select: function (event, ui) {
                event.preventDefault();
                $(this).val(ui.item.value);
                $("input[name='for_type']").val(ui.item.id);
            }
        });
    });

    $('#addModal').on("click", 'input[name="trigger_event_new"]', function () {
        $(this).autocomplete({
            minLength: 1,
            source: "/event-name/autocomplete/0",
            change: function (event, ui) {
                if (!ui.item)
                    $(this).val("");
            },
            select: function (event, ui) {
                event.preventDefault();
                $(this).val(ui.item.label);
                $("input[name='trigger_event']").val(ui.item.value);
            }
        });
    });

    $('#addModal').on("click", 'input[name="condition_event_new"]', function () {
        $(this).autocomplete({
            minLength: 1,
            source: "/event-name/autocomplete/0",
            change: function (event, ui) {
                if (!ui.item)
                    $(this).val("");
            },
            select: function (event, ui) {
                event.preventDefault();
                $(this).val(ui.item.label);
                $("input[name='condition_event']").val(ui.item.value);
            }
        });
    });
    $('#addModal').on("click", 'input[name="responsible_new"]', function () {
        $(this).autocomplete({
            minLength: 2,
            source: "/user/autocomplete",
            change: function (event, ui) {
                if (!ui.item)
                    $(this).val("");
            },
            select: function (event, ui) {
                event.preventDefault();
                $(this).val(ui.item.label);
                $("input[name$='responsible']").val(ui.item.value);
            }
        });
    });

    $('#addModal').on("click", 'input[name="abort_on_new"]', function () {
        $(this).autocomplete({
            minLength: 1,
            source: "/event/autocomplete",
            change: function (event, ui) {
                if (!ui.item)
                    $(this).val("");
            },
            select: function (event, ui) {
                event.preventDefault();
                $(this).val(ui.item.label);
                $("input[name='abort_on']").val(ui.item.value);
            }
        });
    });

    $(document).on("submit", "#createRuleForm", function (e) {
        e.preventDefault();
        var $form = $(this);
        var request = $("#createRuleForm").find("input").filter(function () {
            return $(this).val().length > 0;
        }).serialize(); // Filter out empty values
        var data = request + "&" + $("#createRuleForm").find("textarea").filter(function () {
            return $(this).val().length > 0;
        }).serialize();
        $.post('/rule', data, function (response) {
            if (response.success) {
                window.alert("Rule created.");
                $('#addModal').modal("hide");
            } else {
                associate_errors(response['errors'], $form);
            }
        });
    });


    $(document).on("submit", "#createEventForm", function (e) {
        e.preventDefault();
        var $form = $(this);
        var request = $("#createEventForm").find("input").filter(function () {
            return $(this).val().length > 0;
        }).serialize(); // Filter out empty values
        var data = request + "&" + $("#createEventForm").find("textarea").filter(function () {
            return $(this).val().length > 0;
        }).serialize();
        $.post('/eventname', data, function (response) {
            if (response.success) {
                window.alert("Event name created.");
                $('#addModal').modal("hide");
            } else {
                associate_errors(response['errors'], $form);
            }
        });
    });

    function associate_errors(errors, $form) {
        $form.find('.form-control').removeClass('is-invalid').attr("placeholder", "").find('.help-text').text();
        for (index in errors) {
            value = errors[index][0];
            $form.find('input[name=' + index + '_new]').attr("placeholder", value).attr("title", value).addClass('is-invalid');
            $form.find('input[name=' + index + ']').attr("placeholder", value).attr("title", value).addClass('is-invalid');
        };
    }
</script>
