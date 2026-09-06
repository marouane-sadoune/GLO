import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'

/**
 * Wraps a createResourceApi() instance with the list/get/create/update/remove
 * query hooks every resource page needs, so pages don't hand-roll them.
 */
export function useResourceQueries(key, api) {
  const queryClient = useQueryClient()
  const invalidate = () => queryClient.invalidateQueries({ queryKey: [key] })

  function useList(params, options = {}) {
    return useQuery({ queryKey: [key, 'list', params], queryFn: () => api.list(params), ...options })
  }

  function useOne(id) {
    return useQuery({ queryKey: [key, 'item', id], queryFn: () => api.get(id), enabled: Boolean(id) })
  }

  function useCreate() {
    return useMutation({ mutationFn: (payload) => api.create(payload), onSuccess: invalidate })
  }

  function useUpdate() {
    return useMutation({
      mutationFn: ({ id, payload }) => api.update(id, payload),
      onSuccess: invalidate,
    })
  }

  function useRemove() {
    return useMutation({ mutationFn: (id) => api.remove(id), onSuccess: invalidate })
  }

  return { useList, useOne, useCreate, useUpdate, useRemove }
}
