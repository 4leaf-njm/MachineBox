(function(jsGrid) {

    jsGrid.locales.kr = {
        grid: {
            noDataContent: "데이터가 없습니다.",
            deleteConfirm: "삭제하시겠습니까?",
			pagerFormat: "Pages: {처음} {이전} {pages} {다음} {마지막}    {pageIndex} / {pageCount}",
			pagePrevText: "Prev",
			pageNextText: "Next",
			pageFirstText: "First",
			pageLastText: "Last",
		    pageNavigatorNextText: "...",
			pageNavigatorPrevText: "...",
            loadMessage: "로딩중, 잠시만 기다려주세요...",
            invalidMessage: "잘못된 데이터가 입력되었습니다!"
        },

        loadIndicator: {
            message: "로딩중"
        },

        fields: {
            control: {
				/*
                searchModeButtonTooltip: "검색 으로 전환",
                insertModeButtonTooltip: "입력 으로 전환",
                editButtonTooltip: "편집",
                deleteButtonTooltip: "삭제",
                searchButtonTooltip: "검색",
                clearFilterButtonTooltip: "필터 초기화",
                insertButtonTooltip: "추가",
                updateButtonTooltip: "수정",
                cancelEditButtonTooltip: "수정 취소"
				*/
            }
        },

        validators: {
			/*
            required: { message: "Campo requerido" },
            rangeLength: { message: "La longitud del valor está fuera del intervalo definido" },
            minLength: { message: "La longitud del valor es demasiado larga" },
            maxLength: { message: "La longitud del valor es demasiado corta" },
            pattern: { message: "El valor no se ajusta al patrón definido" },
            range: { message: "Valor fuera del rango definido" },
            min: { message: "Valor demasiado alto" },
            max: { message: "Valor demasiado bajo" }*/
        }

    };

}(jsGrid, jQuery));
