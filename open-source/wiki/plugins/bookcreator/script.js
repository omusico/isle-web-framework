var Bookcreator = {
    cookieName: 'bookcreator',

    /**
     * Handler add or removes page to selection, called via buttons of toolbar
     */
    togglePageSelection: function (e) {
        e.preventDefault();
        //add or remove
        var addORremove = (jQuery(this).parent().attr('id').substr(13) == 'add');

        //update cookie and counter
        Bookcreator.storePageStatus(JSINFO.id, addORremove);
        Bookcreator.storePageOrder();
        jQuery("#bookcreator__pages").html(Bookcreator.countPages());

        //toggle add/remove in UI
        jQuery("#bookcreator__add").toggle(!addORremove);
        jQuery("#bookcreator__remove").toggle(addORremove);

        Bookcreator.updatePagetoolLink();
    },

    /**
     * Handler moves pages between the lists of selected and removed pages
     */
    movePage: function () {
        var $a = jQuery(this),
            $li = $a.parent(),
            pageid = $li.attr('id').substr(4);

        //true=add to selected list, false=remove from selected list
        var addORremove = $li.parent().hasClass('remove');

        //move page to other list
        var listclass = addORremove ? 'include' : 'remove';
        $li.appendTo(jQuery('div.bookcreator__pagelist ul.pagelist.' + listclass));

        //store new status in cookie
        Bookcreator.storePageStatus(pageid, addORremove);
        Bookcreator.storePageOrder();

        //update interface
        $a.attr('title', LANG.plugins.bookcreator[addORremove ? 'remove' : 'include']);
        Bookcreator.toggleDeletelist();
    },

    /**
     * Show/hide list of delete pages when empty
     */
    toggleDeletelist: function () {
        var show = jQuery('div.bookcreator__pagelist ul.pagelist.remove li').length > 0;
        jQuery('#bookcreator__delpglst ul').toggleClass('hint', !show)
    },

    /**
     * Counts the pages
     *
     * @return {Number} number of pages
     */
    countPages: function () {
        var k = 0;
        var cookies = document.cookie.split("; ");
        jQuery.each(cookies, function (i, cookie) {
            if (cookie.substr(0, Bookcreator.cookieName.length) == Bookcreator.cookieName) {
                var parts = cookie.split('=');
                if (parts[1] == 1) {
                    k = k + 1;
                }
            }
        });
        return k;
    },

    /**
     * Set cookie for Bookcreator
     *
     * @param {String} pageid whole id of page
     * @param {Boolean} addORremove whether page should be added to selection, null is removing cookie
     */
    storePageStatus: function (pageid, addORremove) {
        var key = Bookcreator.cookieName + '[' + pageid + ']',
            days = 7,
            path = (typeof DOKU_COOKIEPATH === "undefined" ? JSINFO.DOKU_COOKIEPATH : DOKU_COOKIEPATH );

        if (addORremove === null) {
            days = -1;
        }
        var value = String(addORremove ? 1 : 0);

        var t = new Date();
        t.setDate(t.getDate() + days);
        return (document.cookie = [
            key, '=', encodeURIComponent(value),
            '; expires=' + t.toUTCString(), // use expires attribute, max-age is not supported by IE
            '; path=' + path
        ].join(''));
    },
    storePageOrder: function () {
        var pagelist = [],
            path = (typeof DOKU_COOKIEPATH === "undefined" ? JSINFO.DOKU_COOKIEPATH : DOKU_COOKIEPATH );

        jQuery('div.bookcreator__pagelist ul.pagelist.include li').each(function () {
            pagelist.push(jQuery(this).attr('id').substr(4));
        });
        jQuery.cookie.raw = true;

        jQuery.cookie("list-pagelist", pagelist.join('|'), { expires: 7, path:  path });
    },

    /**
     * List item is dropped in other pagelist
     * @param event
     * @param ui
     */
    droppedInOtherlist: function (event, ui) {
        var pageid = ui.item.attr('id').substr(4),
            addORremove = ui.item.parent().hasClass('include');

        //store new status in cookie
        Bookcreator.storePageStatus(pageid, addORremove);
        //update layout
        ui.item.children('a.action').attr('title', LANG.plugins.bookcreator[addORremove ? 'remove' : 'include']);
        Bookcreator.toggleDeletelist()
    },

    /**
     * Handle read or delete of a Selection from the Selection List
     */
    actionList: function () {
        var $this = jQuery(this),
            action = ($this.hasClass('delete') ? 'delete' : 'read'),
            pageid = $this.parent().attr('id').substr(5);

        //confirm dialog
        var msg,
            confirmrequired = true,
            comfirmed = true;
        if (action == "delete") {
            msg = LANG.plugins.bookcreator.confirmdel;
        } else {
            if (Bookcreator.countPages() == 0) {
                confirmrequired = false;
            }
            msg = LANG.plugins.bookcreator.confirmload;
        }

        if (confirmrequired) comfirmed = confirm(msg);
        if (comfirmed) {
            //special do action is handled in action.php, otherwise task is handled in syntax.php
            document.bookcreator__selections__list.do.value = (action == 'read' ? 'readsavedselection' : 'show');
            document.bookcreator__selections__list.task.value = action;
            document.bookcreator__selections__list.page.value = pageid;
            document.bookcreator__selections__list.submit();
            return true;
        }
    },
    /**
     * Add addtobook button to pagetools of 'dokuwiki' template,when its exists
     */
    addPagetoolLink: function () {
        //is pagetools available
        var pgtools = jQuery('#dokuwiki__pagetools ul');
        if (pgtools.length && JSINFO && JSINFO.hasbookcreatoraccess) {
            //is page shown
            if(!jQuery('#dokuwiki__pagetools ul a.action.show').length && JSINFO.wikipagelink != undefined) {
                var $a = jQuery('<a class="action addtobook" rel="nofollow"></a>')
                    .attr('href', JSINFO.wikipagelink + (JSINFO.wikipagelink.indexOf("?") < 0 ? '?' : '&') +"do=addtobook")
                    .attr('title', LANG.plugins.bookcreator.btn_addtobook)
                    .append(jQuery('<span>' + LANG.plugins.bookcreator.btn_addtobook + '</span>'));
                var $li = jQuery('<li></li>').append($a);

                //insert above top link
                jQuery('#dokuwiki__pagetools ul li a.action.top').parent().before($li);
                Bookcreator.updatePagetoolLink();
            }
        }
    },
    /**
     * Toggle addtobook button between add and remove
     */
    updatePagetoolLink: function () {
        //is bookcreator toolbar available
        var $bkcrtr = jQuery('.bookcreator__');
        if ($bkcrtr.length) {
            var addORremove = $bkcrtr.find("#bookcreator__add").is(':visible');
            var $addtobookbtn = jQuery('#dokuwiki__pagetools ul a.action.addtobook');
            //exist the addtobook link
            if ($addtobookbtn.length) {
                var text = LANG.plugins.bookcreator['btn_' + (addORremove ? 'add' : 'remove') + 'tobook'];
                $addtobookbtn.toggleClass('remove', !addORremove)
                    .attr('title', text)
                    .children('span').html(text);
            }
        }
    }
};


jQuery(function () {
    //bookcreator toolbar
    jQuery('a.bookcreator__tglPgSelection').click(Bookcreator.togglePageSelection);
    Bookcreator.addPagetoolLink();

    //bookmanager
    var $pagelist = jQuery('div.bookcreator__pagelist');
    if ($pagelist.length) {
        $pagelist.find('a.action').click(Bookcreator.movePage);
        $pagelist.find('ul.pagelist.include,ul.pagelist.remove').sortable({
            connectWith: "div.bookcreator__pagelist ul.pagelist",
            receive: Bookcreator.droppedInOtherlist,
            stop: Bookcreator.storePageOrder,
            distance: 5
        });

        Bookcreator.toggleDeletelist();
        Bookcreator.storePageOrder()
    }

    //add click handlers to Selectionslist
    jQuery('form#bookcreator__selections__list a.action').click(Bookcreator.actionList);
});
