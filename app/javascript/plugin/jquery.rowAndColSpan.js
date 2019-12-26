/**
 * Closure - The jQuery object will be passed to an anonymous function.
 * $ will be an alias for the jQuery object to use in our plugin.
 */
(function($) {
    
    /**
     * ColSpan plugin definination.
     */
    $.fn.colSpan = function () {
        // Define variables.
        var $this, $targetTD, colSpan, selector;
    
        // Iterate each matched element.
        return this.each(function () {
            // Iterates the elements that has a class name that contains 'colspan'. 
            $('[class^="colspan"]', $(this)).each(function () {
                // Store the element in a variable.
                $this = $(this);
                
                // Get the closest <TD>-element.
                $targetTD = $this.closest("td");
                
                // Gets the colspan value from the class name.
                colSpan = $this.attr("class").substring(7, 8);
    
                // If the value is a number we will apply colspan.
                if (Number(colSpan) != NaN) {
                    // Add colspan attribute to the parent <TD>.
                    $targetTD.attr("colspan", colSpan);
    
                    // Build a selector that will give us a limited number  
                    // of <TD>-elements using the colspan value.
                    selector = "td:lt(" + (colSpan - 1) + ")";
    
                    // Remove <TD>-elements to the right.
                    $targetTD.nextAll(selector).remove("td");
                }
            });
        });
    };
    
    
    /**
     * RowSpan plugin definination.
     */
    $.fn.rowSpan = function () {
        // Define variables.
        var $this, $targetTD, $targetTR, targetTDIndex, rowSpan, selector;
    
        // Iterate each matched element.
        return this.each(function () {
            // Iterates the elements that has a class name that contains 'rowspan'. 
            $('[class^="rowspan"]', $(this)).each(function () {
                // Store the element in a variable.
                $this = $(this);
                
                // Get the closest <TD>-element.
                $targetTD = $this.closest("td");
                
                // Get the closest <TR>-element.
                $targetTR = $this.closest("tr");
                
                // Gets the rowspan value from the class name.
                rowSpan = $this.attr("class").substring(7, 8);
    
                // If the value is a number we will apply colspan.
                if (Number(rowSpan) != NaN) {
                    // Add rowspan attribute to the parent <TD>.
                    $targetTD.attr("rowspan", rowSpan);
                    
                    // Gets the index number of the parent <TD>-element.
                    targetTDIndex = $targetTD.index();
    
                    // Build a selector that will give us a limited number  
                    // of <TR>-elements using the rowspan value.
                    selector = "tr:lt(" + (rowSpan - 1) + ")";
                    
                    // Iterate the table rows.
                    $targetTR.nextAll(selector).each(function () {
                        // The query selects a TD-element on each table row that 
                        // equals the target TD index and removes it.
                        $("td:eq(" + targetTDIndex + ")", $(this)).remove();
                    });
                }
            });
        });
    };

})(jQuery);