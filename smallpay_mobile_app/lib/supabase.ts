import AsyncStorage from '@react-native-async-storage/async-storage';

// Mock Supabase Client pour développement local
class MockSupabaseClient {
  private data: Record<string, any[]> = {
    users: [],
    products: [],
    orders: [],
    payments: [],
    payment_schedules: [],
  };

  private async loadData() {
    try {
      const stored = await AsyncStorage.getItem('supabase_data');
      if (stored) {
        this.data = JSON.parse(stored);
      }
    } catch (error) {
      console.error('Error loading data:', error);
    }
  }

  private async saveData() {
    try {
      await AsyncStorage.setItem('supabase_data', JSON.stringify(this.data));
    } catch (error) {
      console.error('Error saving data:', error);
    }
  }

  from(table: string) {
    return {
      select: (columns?: string) => ({
        eq: (field: string, value: any) => ({
          eq: (field2: string, value2: any) => ({
            single: async () => {
              await this.loadData();
              const record = this.data[table]?.find(
                (r) => r[field] === value && r[field2] === value2
              );
              return { data: record || null, error: null };
            },
          }),
          single: async () => {
            await this.loadData();
            const record = this.data[table]?.find((r) => r[field] === value);
            return { data: record || null, error: null };
          },
          order: (field: string, options: any) => ({
            then: async (callback: any) => {
              await this.loadData();
              const filtered = this.data[table]?.filter((r) => r[field] === value) || [];
              callback({ data: filtered, error: null });
            },
          }),
        }),
        order: (field: string, options: any) => ({
          then: async (callback: any) => {
            await this.loadData();
            const sorted = [...(this.data[table] || [])].sort((a, b) => {
              if (options.ascending === false) {
                return b[field] > a[field] ? 1 : -1;
              }
              return a[field] > b[field] ? 1 : -1;
            });
            callback({ data: sorted, error: null });
          },
        }),
        single: async () => {
          await this.loadData();
          return { data: this.data[table]?.[0] || null, error: null };
        },
        then: async (callback: any) => {
          await this.loadData();
          callback({ data: this.data[table] || [], error: null });
        },
      }),
      eq: (field: string, value: any) => ({
        then: async (callback: any) => {
          await this.loadData();
          const filtered = this.data[table]?.filter((r) => r[field] === value) || [];
          callback({ data: filtered, error: null });
        },
      }),
      insert: (records: any[]) => ({
        select: () => ({
          single: async () => {
            await this.loadData();
            const id = Math.random().toString(36).substr(2, 9);
            const newRecord = { id, ...records[0], created_at: new Date().toISOString() };
            this.data[table] = this.data[table] || [];
            this.data[table].push(newRecord);
            await this.saveData();
            return { data: newRecord, error: null };
          },
          then: async (callback: any) => {
            await this.loadData();
            const newRecords = records.map((r) => ({
              id: Math.random().toString(36).substr(2, 9),
              ...r,
              created_at: new Date().toISOString(),
            }));
            this.data[table] = this.data[table] || [];
            this.data[table].push(...newRecords);
            await this.saveData();
            callback({ data: newRecords, error: null });
          },
        }),
        then: async (callback: any) => {
          await this.loadData();
          const newRecords = records.map((r) => ({
            id: Math.random().toString(36).substr(2, 9),
            ...r,
            created_at: new Date().toISOString(),
          }));
          this.data[table] = this.data[table] || [];
          this.data[table].push(...newRecords);
          await this.saveData();
          callback({ data: newRecords, error: null });
        },
      }),
      update: (updates: any) => ({
        eq: (field: string, value: any) => ({
          then: async (callback: any) => {
            await this.loadData();
            this.data[table] = this.data[table]?.map((r) =>
              r[field] === value ? { ...r, ...updates } : r
            );
            await this.saveData();
            callback({ data: null, error: null });
          },
        }),
      }),
    };
  }
}

export const supabase = new MockSupabaseClient();
