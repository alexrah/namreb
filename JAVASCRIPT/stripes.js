  // Handlebars.registerHelper('stripeRows', function (rows, fn) {
  function stripes (rows, options){
        var buffer = [],
            i, len;
        
        for (i = 0, len = rows.length; i < len; ++i) {
            var row = rows[i];
            row.stripeClass = (i + 1) % 2 === 0 ? 'even' : 'odd';

            // Render the block once for each row.
            // buffer.push(fn(row));
            buffer += options.fn(row);
        }
        
        return buffer;
    }
