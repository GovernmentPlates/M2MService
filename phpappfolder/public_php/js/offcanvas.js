/*
* Off Canvas (mobile navigation menu)
* Author: GetBootstrap.com Authors
 */
(function () {
  'use strict'

  document.querySelector('#navbarSideCollapse').addEventListener('click', function () {
    document.querySelector('.offcanvas-collapse').classList.toggle('open')
  })
})()