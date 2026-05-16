(function ($) {
    if (!$) {
        return;
    }

    const formatMoney = (value) => {
        const number = Number.parseFloat(value || 0);

        return (window.saleFormConfig?.currencyFormatter ?? new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL',
        })).format(Number.isNaN(number) ? 0 : number);
    };

    const getSaleItemsTotal = () => $('#items-table tbody .item-row').toArray().reduce((sum, element) => {
        const row = $(element);

        return sum + ((Number.parseFloat(row.find('.item-quantity').val()) || 0) * (Number.parseFloat(row.find('.item-unit-price').val()) || 0));
    }, 0);

    const setItemNames = () => {
        $('#items-table tbody .item-row').each((index, element) => {
            $(element).find('.product-select').attr('name', `items[${index}][product_id]`);
            $(element).find('.item-quantity').attr('name', `items[${index}][quantity]`);
            $(element).find('.item-unit-price').attr('name', `items[${index}][unit_price]`);
        });
    };

    const setInstallmentNames = () => {
        $('#installments-table tbody .installment-row').each((index, element) => {
            $(element).find('.installment-payment-method').attr('name', `installments[${index}][payment_method_id]`);
            $(element).find('.installment-date').attr('name', `installments[${index}][due_date]`);
            $(element).find('.installment-amount').attr('name', `installments[${index}][amount]`);
            $(element).find('.installment-status').attr('name', `installments[${index}][status]`);
        });
    };

    const updateTotals = () => {
        let saleTotal = 0;

        $('#items-table tbody .item-row').each((_, element) => {
            const row = $(element);
            const quantity = Number.parseFloat(row.find('.item-quantity').val()) || 0;
            const unitPrice = Number.parseFloat(row.find('.item-unit-price').val()) || 0;
            const total = quantity * unitPrice;

            saleTotal += total;
            row.find('.item-total').text(formatMoney(total));
        });

        $('#sale-total').text(formatMoney(saleTotal));
    };

    const syncItemUnitPrice = (row) => {
        const productSelect = row.find('.product-select');
        const selectedPrice = productSelect.find(':selected').data('price');
        const unitPriceInput = row.find('.item-unit-price');

        if (selectedPrice === undefined || selectedPrice === null || selectedPrice === '') {
            unitPriceInput.val('');
            return;
        }

        unitPriceInput.val(selectedPrice);
    };

    const appendItemRow = (values = {}) => {
        const row = $($('#item-row-template').html());

        row.find('.product-select').val(values.product_id ?? '');
        row.find('.item-quantity').val(values.quantity ?? 1);
        row.find('.item-unit-price').val(values.unit_price ?? '');

        if ((values.unit_price === undefined || values.unit_price === null || values.unit_price === '') && values.product_id) {
            syncItemUnitPrice(row);
        }

        $('#items-table tbody').append(row);
        setItemNames();
        updateTotals();
    };

    const appendInstallmentRow = (values = {}) => {
        const row = $($('#installment-row-template').html());

        row.find('.installment-payment-method').val(values.payment_method_id ?? '');
        row.find('.installment-date').val(values.due_date ?? '');
        row.find('.installment-amount').val(values.amount ?? '');
        row.find('.installment-status').val(values.status ?? 'pending');

        $('#installments-table tbody').append(row);
        setInstallmentNames();
    };

    const generateInstallments = () => {
        const count = Number.parseInt($('#installments-count').val(), 10) || 1;
        const firstDueDate = $('#first-due-date').val();
        const saleTotal = getSaleItemsTotal();

        if (!firstDueDate || saleTotal <= 0) {
            return;
        }

        const tbody = $('#installments-table tbody');
        tbody.empty();

        const baseValue = Math.floor((saleTotal / count) * 100) / 100;
        let remaining = Number(saleTotal.toFixed(2));
        const dueDate = new Date(`${firstDueDate}T00:00:00`);

        for (let index = 0; index < count; index += 1) {
            const installmentAmount = index === count - 1 ? remaining : baseValue;
            remaining = Number((remaining - installmentAmount).toFixed(2));

            const currentDate = new Date(dueDate);
            currentDate.setMonth(currentDate.getMonth() + index);

            appendInstallmentRow({
                payment_method_id: '',
                due_date: currentDate.toISOString().slice(0, 10),
                amount: installmentAmount.toFixed(2),
                status: 'pending',
            });
        }
    };

    const rebalanceInstallments = (changedRow) => {
        const rows = $('#installments-table tbody .installment-row');

        if (rows.length <= 1) {
            return;
        }

        const saleTotal = Number(getSaleItemsTotal().toFixed(2));

        if (saleTotal <= 0) {
            return;
        }

        const changedIndex = rows.index(changedRow);

        if (changedIndex === -1) {
            return;
        }

        const previousRows = rows.slice(0, changedIndex);
        const nextRows = rows.slice(changedIndex + 1);
        const nextCount = nextRows.length;
        const minimumPerInstallment = 0.01;
        const previousTotal = previousRows.toArray().reduce((sum, element) => {
            const value = Number.parseFloat($(element).find('.installment-amount').val()) || 0;

            return sum + value;
        }, 0);
        const maxCurrentAmount = nextCount > 0
            ? Math.max(saleTotal - previousTotal - (nextCount * minimumPerInstallment), minimumPerInstallment)
            : Math.max(saleTotal - previousTotal, minimumPerInstallment);
        const amountInput = changedRow.find('.installment-amount');
        const rawAmount = Number.parseFloat(amountInput.val());
        const currentAmount = Math.min(
            Math.max(Number.isNaN(rawAmount) ? minimumPerInstallment : rawAmount, minimumPerInstallment),
            maxCurrentAmount
        );

        amountInput.val(currentAmount.toFixed(2));

        if (nextCount === 0) {
            return;
        }

        const remaining = Number((saleTotal - previousTotal - currentAmount).toFixed(2));
        const baseAmount = Math.floor((remaining / nextCount) * 100) / 100;
        let leftover = Number(remaining.toFixed(2));

        nextRows.each((index, element) => {
            const amount = index === nextCount - 1 ? leftover : baseAmount;
            const normalizedAmount = Math.max(amount, minimumPerInstallment);

            leftover = Number((leftover - amount).toFixed(2));
            $(element).find('.installment-amount').val(normalizedAmount.toFixed(2));
        });
    };

    const hasEmptySaleRows = () => {
        let firstInvalidField = null;

        $('#items-table tbody .item-row').each((_, element) => {
            $(element).find('.product-select, .item-quantity, .item-unit-price').each((__, field) => {
                const input = $(field);

                if (firstInvalidField || `${input.val() ?? ''}`.trim() !== '') {
                    return;
                }

                firstInvalidField = input;
            });
        });

        $('#installments-table tbody .installment-row').each((_, element) => {
            $(element).find('.installment-payment-method, .installment-date, .installment-amount, .installment-status').each((__, field) => {
                const input = $(field);

                if (firstInvalidField || `${input.val() ?? ''}`.trim() !== '') {
                    return;
                }

                firstInvalidField = input;
            });
        });

        if (!firstInvalidField) {
            return false;
        }

        firstInvalidField.trigger('focus');
        window.alert('Preencha todos os campos dos itens e das parcelas antes de salvar a venda.');

        return true;
    };

    $(function () {
        if (!$('#sale-form').length) {
            return;
        }

        setItemNames();
        setInstallmentNames();
        updateTotals();

        $('#add-item').on('click', () => appendItemRow());
        $('#add-installment').on('click', () => appendInstallmentRow({ due_date: $('#first-due-date').val() }));
        $('#generate-installments').on('click', generateInstallments);

        $(document).on('change', '.product-select', function () {
            const row = $(this).closest('.item-row');
            syncItemUnitPrice(row);
            updateTotals();
        });

        $(document).on('input', '.item-quantity, .item-unit-price', updateTotals);
        $(document).on('change', '.installment-amount', function () {
            rebalanceInstallments($(this).closest('.installment-row'));
        });

        $(document).on('click', '.remove-item', function () {
            if ($('#items-table tbody .item-row').length === 1) {
                return;
            }

            $(this).closest('.item-row').remove();
            setItemNames();
            updateTotals();
        });

        $(document).on('click', '.remove-installment', function () {
            if ($('#installments-table tbody .installment-row').length === 1) {
                return;
            }

            $(this).closest('.installment-row').remove();
            setInstallmentNames();
        });

        $(document).on('click', '.edit-installment-inline', function () {
            const row = $(this).closest('.installment-row');
            const paymentMethodSelect = row.find('.installment-payment-method');

            paymentMethodSelect.trigger('focus');

            if (!paymentMethodSelect.val()) {
                paymentMethodSelect.trigger('click');
            }
        });

        $('#sale-form').on('submit', function (event) {
            const form = this;

            if (!form.checkValidity()) {
                event.preventDefault();
                form.reportValidity();
                return;
            }

            if (hasEmptySaleRows()) {
                event.preventDefault();
            }
        });
    });
}(window.jQuery));
