import iziToast from 'izitoast'

export function useIziToast () {
  return {
    success (message: string, title = 'Success') {
      iziToast.success({ title, message })
    },
    error (message: string, title = 'Error') {
      iziToast.error({ title, message })
    }
  }
}
