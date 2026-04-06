import { toRefs } from "vue";

export function storeToRefs<T extends object>(store: T) {
  return toRefs(store);
}
