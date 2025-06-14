class DynamicRow {
  constructor(parent, onRowAdded = null, onRowRemoved = null) {
    this.$parent = $(parent);
    this.addRowClass = this.$parent.data("add_row_class")
      ? `.${this.$parent.data("add_row_class")}`
      : ".add-row";
    this.removeRowClass = this.$parent.data("remove_row_class")
      ? `.${this.$parent.data("remove_row_class")}`
      : ".remove-row";
    this.max = this.$parent.data("max") ? this.$parent.data("max") : Infinity;

    this.onRowAdded = onRowAdded;
    this.onRowRemoved = onRowRemoved;

    this.init();

    this.bindEvents();
    this.bindGlobalEvents();
  }

  init() {
    this.$parent.find(this.addRowClass).addClass("d-none");
    if (this.$parent.find(".clone_row").length === 1) {
      this.$parent.find(this.removeRowClass).addClass("d-none");
    }
    this.$parent.find(this.addRowClass).last().removeClass("d-none");

    this.updateDayCounts();

    this.initPlugins();
  }

  initPlugins() {
    this.initCk();
    this.initSelectize();
    this.initDateTimepicker();
    this.initFlatpickr();
  }

  destroyPlugins() {
    this.destroyCk();
    this.destroySelectize();
    this.destroyDateTimepicker();
    this.destroyFlatpickr();
  }

  bindEvents() {
    this.$parent.on("click", this.addRowClass, () => this.addRow());
    this.$parent.on("click", this.removeRowClass, (event) =>
      this.removeRow(event)
    );

    this.$parent.find('.numInput').each((index, element) => {
      $(element).on("keypress", function () {
        return /\d/.test(String.fromCharCode(event.keyCode || event.which));
      });
    });
  }

  bindGlobalEvents() {
    $(document).on("keydown", (event) => {
      // Add row on Ctrl + ArrowDown
      if (event.ctrlKey && event.key === "ArrowDown") {
        if (
          this.$parent.is(":focus") ||
          $(event.target).closest(this.$parent).length
        ) {
          event.preventDefault();
          this.addRow();
        }
      }

      // Remove row on Ctrl + D
      if (event.ctrlKey && event.key === "d") {
        if (
          this.$parent.is(":focus") ||
          $(event.target).closest(this.$parent).length
        ) {
          event.preventDefault();
          this.removeFocusedRow();
        }
      }
    });
  }

  addRow() {
    this.$parent.find(this.addRowClass).addClass("d-none");
    this.$parent.find(this.removeRowClass).last().removeClass("d-none");

    let html = this.$parent.find(".clone_row").last();
    html.find(".day_content").attr("id");
    this.destroyPlugins();

    html = html.clone();
    let day = this.$parent.children(".clone_row").length + 1;
    let newCk = `day_${day}_content`;

    html.find(this.removeRowClass).removeClass("d-none");
    html.find(this.addRowClass).removeClass("d-none");

    html.find("input,select").not(".keep_value").val("");
    html.find("textarea").not(".keep_value").val("");

    html.find('.remove_this').remove();

    html.attr("id", day);

    if (this.max && this.$parent.children(".clone_row").length >= this.max) {
      return;
    } else {
      this.$parent.append(html);
    }

    if (this.$parent.children(".clone_row").length === this.max) {
      $(this.addRowClass).last().addClass("d-none");
    }

    this.updateDayCounts();

    this.initPlugins();
    if (typeof this.onRowAdded === "function") {
      this.onRowAdded();
    }
  }

  removeRow(event) {
    let $currentRow = $(event.currentTarget).closest(".clone_row");
    this.deleteRow($currentRow);
    if (typeof this.onRowRemoved === "function") {
      this.onRowRemoved();
    }
  }

  removeFocusedRow() {
    let $focusedRow = this.$parent.find(":focus").closest(".clone_row");
    let prev = $focusedRow.prev();
    let next = $focusedRow.next();
    if ($focusedRow.length && this.$parent.children(".clone_row").length > 1) {
      this.deleteRow($focusedRow);
    }

    if (prev.length && next.length) {
      prev.find("input").first().focus();
      return;
    }

    if (prev.length) {
      prev.find("input").first().focus();
    }
    if (next.length) {
      next.find("input").first().focus();
    }
  }

  deleteRow($row) {
    let $last = this.$parent.children(".clone_row").last();
    let $first = this.$parent.children(".clone_row").first();

    if ($last.attr("id") === $row.attr("id")) {
      $row.prev().find(this.addRowClass).removeClass("d-none");
    }
    if (
      $first.attr("id") === $row.attr("id") &&
      this.$parent.children(".clone_row").length === 2
    ) {
      $row.next().find(this.removeRowClass).addClass("d-none");
    }
    if (this.$parent.children(".clone_row").length === 2) {
      $row.prev().find(this.removeRowClass).addClass("d-none");
    }

    $row.remove();

    this.updateDayCounts();
  }

  updateDayCounts() {
    this.$parent.find(".clone_row").each((index, element) => {
      let count = index + 1;
      $(element).attr("id", count);
      this.renameInputs($(element), count);
    });
  }

  destroyCk() {
    this.$parent.find(".ckeditor").each((_, element) => {
      let id = $(element).attr("id");
      if (window[id] && window[id].editor) {
        window[id].editor.destroy();
      }
    });
  }

  initCk() {
    this.$parent.find(".ckeditor").each((_, element) => {
      let id = $(element).attr("id");
      if (typeof initCk === "function") {
        initCk(id);
      }
    });
  }

  destroySelectize() {
    this.$parent.find(".selectize").each((_, element) => {
      let value = $(element).val();
      let selectize = $(element).selectize()[0].selectize;
      selectize.destroy();
      $(element).val(value.length === 0 ? "" : value);
    });
  }

  initSelectize() {
    this.$parent.find(".selectize").each((_, element) => {
      if (element.selectize) {
        element.selectize.destroy();
        $(element).val("");
      }

      $(element).selectize({
        plugins: ["remove_button"],
        delimiter: ",",
        persist: false,
      });
    });
  }

  destroyDateTimepicker() {
    this.$parent.find(".dateTimepicker").each((_, element) => {
      let value = $(element).val();
      let id = $(element).attr("id");
      if (window[id]) {
        window[id].destroy();
      }
      $(element).val(value.length === 0 ? "" : value);
    });
  }

  initDateTimepicker() {
    this.$parent.find(".dateTimepicker").each((_, element) => {
      let id = $(element).attr("id");
      if (typeof initDateTimepicker === "function") {
        this.initDateTimepickerFunction(id);
      }
    });
  }

  initDateTimepickerFunction(id, { minDate, maxDate, mode } = {}) {
    let element = document.getElementById(id);
    if (element) {
      let options = {
        dateFormat: "d-m-Y H:i",
        minDate: minDate,
        maxDate: maxDate,
        enableTime: true,
      };
      if (mode) {
        options.mode = mode;
      }
      if (minDate && maxDate) {
        options.enableTime = true;
      }
      if (minDate) {
        minDate = new Date(minDate);
        options.minTime = minDate;
        options.minDate = minDate;
      }
      if (maxDate) {
        maxDate = new Date(maxDate);
        options.maxTime = maxDate;
        options.maxDate = maxDate;
      }
      window[id] = flatpickr(element, options);
    }
  }

  initFlatpickr() {
    this.$parent.find(".flatpickr").each((_, element) => {
      let id = $(element).attr("id");
      if (!id) {
        let random_id = `${(Math.random() + 1).toString(36).substring(7)}_${_}`;
        $(element).attr("id", random_id);
        id = random_id;
      }
      window[id] = flatpickr(element, {
        dateFormat: "d-m-Y",
        onChange: function () {
          $(this).trigger("change");
        },
        onOpen: function () {
          $(this).trigger("change");
        },
      });
    });
  }

  destroyFlatpickr() {
    this.$parent.find(".flatpickr").each((_, element) => {
      let id = $(element).attr("id");
      if (window[id]) {
        window[id].destroy();
      }
    });
  }

  renameInputs($parent, count) {
    $parent.find(".day_count,.counter").html(count);
    $parent.find("input, select, textarea").each((_, element) => {
      let $el = $(element);
      if ($el.attr("id")) {
        let id = $el.attr("id").replace(/\d+/, count);
        $el.attr("id", id);
      }
    });
  }
}
