import { computed, type MaybeRefOrGetter, toValue } from "vue";

type ClassValue =
  | string
  | number
  | null
  | undefined
  | false
  | ClassValue[]
  | Record<string, boolean | null | undefined>;

function flattenClass(value: ClassValue, output: string[]) {
  if (!value) return;

  if (typeof value === "string" || typeof value === "number") {
    output.push(String(value));
    return;
  }

  if (Array.isArray(value)) {
    for (const item of value) {
      flattenClass(item, output);
    }
    return;
  }

  for (const [key, enabled] of Object.entries(value)) {
    if (enabled) output.push(key);
  }
}

export function cn(...inputs: ClassValue[]) {
  const output: string[] = [];

  for (const input of inputs) {
    flattenClass(input, output);
  }

  return output.join(" ");
}

export function useForwardPropsEmits<T>(props: T) {
  return computed(() => props);
}

export function toReactiveValue<T>(value: MaybeRefOrGetter<T>) {
  return toValue(value);
}
