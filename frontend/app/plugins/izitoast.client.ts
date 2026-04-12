import iziToast from 'izitoast'

export default defineNuxtPlugin(() => {
  iziToast.settings({
    position: 'topRight',
    timeout: 4500,
    resetOnHover: true,
    transitionIn: 'fadeInDown',
    transitionOut: 'fadeOutUp'
  })
})
